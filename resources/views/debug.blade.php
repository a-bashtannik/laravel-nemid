<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NemID Debug Tool</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"
            integrity="sha512-bZS47S7sPOxkjU/4Bt0zrhEtWx0y0CRkhEp8IckzK+ltifIIE9EMIMTuT/mEzoIMewUINruDBIR/jJnbguonqQ=="
            crossorigin="anonymous"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-wEmeIV1mKuiNpC+IOBjI7aAzPcEZeedi5yW5f2yOq55WWLwNGmvvx4Um1vskeMj0" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-p34f1UUtsS3wqzfto5wAAmdvj+osOnFyQFpp4Ua3gs/ZVWx6oOypYoCJhGGScy+8"
            crossorigin="anonymous"></script>
</head>
<body>
<div class="container m-5">
    <nav>
        <div class="nav nav-tabs" id="nav-tab" role="tablist">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab">
                Home
            </button>
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab">
                JavaScript (POCES)
            </button>
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#nav-contact" type="button" role="tab">
                CodeFile (MOCES)
            </button>
        </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane show active m-3" id="nav-home">
            <h3>NemID Applet parameters</h3>
            <pre>
JavaScript Endpoint URL: {!! \Rentdesk\Nemid\Facades\Nemid::javascriptClient()->getEndpoint() !!}
CodeFile Endpoint URL: {!! \Rentdesk\Nemid\Facades\Nemid::codefileClient()->getEndpoint()  !!}
            </pre>
            <h3>NemID Config dump</h3>
            <pre>
@php
print_r(config('nemid'));
@endphp
            </pre>
        </div>
        <div class="tab-pane m-3" id="nav-profile">
            <div class="row">
                <div class="col">
                    <h3>NemID JavaScript applet</h3>
                    <iframe id="nemid_iframe" scrolling="no" width="205px" height="255px"></iframe>
                </div>
                <div class="col">
                    <h3>Response console</h3>
                    <textarea id="nemid_console" class="border w-100 h-100"></textarea>
                </div>
            </div>
        </div>
        <div class="tab-pane m-3" id="nav-contact">
            <div class="row">
                <div class="col">
                    <h3>NemID CodeFile applet</h3>
                    <p>
                    Install <a href="https://www.medarbejdersignatur.dk/nemid-noglefilsprogram/download/index.html">NemID Software</a>
                    </p>
                    <p>
                    Use <code>tests/fixtures/nemid-MOCES-employee-valid.p12</code> certificate for tests.
                    </p>
                    <iframe id="codefile_iframe" scrolling="no" width="100%" height="300px"></iframe>
                </div>
                <div class="col">
                    <h3>Response console</h3>
                    <textarea id="codefile_console" class="border w-100 h-100"></textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

    (function () {
        window.nemidJSUrl = "{!! \Rentdesk\Nemid\Facades\Nemid::javascriptClient()->getEndpoint() !!}";
        window.nemidCFUrl = "{!! \Rentdesk\Nemid\Facades\Nemid::codefileClient()->getEndpoint() !!}";

        document.getElementById("nemid_iframe").src = window.nemidJSUrl;
        document.getElementById("codefile_iframe").src = window.nemidCFUrl;

        console.info('Loading NemID JavaScript Client config');

        axios.get('/nemid/javascript')
            .then(function (response) {
                window.nemidJSConfig = response.data;
                console.log('NemID JavaScript config', response.data);
            })

        axios.get('/nemid/codefile')
            .then(function (response) {
                window.nemidCFConfig = response.data;
                console.log('NemID CodeFile config', response.data);
            })


        function onNemIDMessage(e) {
            var event = e || event;
            var nemid_iframe = document.getElementById("nemid_iframe").contentWindow;
            var nemid_origin = (new URL(window.nemidJSUrl)).origin;

            var codefile_iframe = document.getElementById("codefile_iframe").contentWindow;
            var codefile_origin = (new URL(window.nemidCFUrl)).origin;

            var message;

            try {
                message = JSON.parse(event.data);
            } catch (e) {
                return;
                /*ignore not JSON */
            }

            if (event.origin === nemid_origin) {

                // Nemid Javascript Client flow

                if (message.command === "SendParameters") {

                    var postMessage = {}

                    postMessage.command = "parameters";
                    postMessage.content = JSON.stringify(window.nemidJSConfig);

                    nemid_iframe.postMessage(JSON.stringify(postMessage), nemid_origin);
                }

                if (message.command === 'changeResponseAndSubmit') {
                    let response = atob(message.content);

                    if (response.match(/^APP/) || response.match(/^SRV/)) {
                        console.error("NEMID ERROR: " + response);
                        return;
                    }

                    if (response.substr(0, 5) === "\<\?xml") {
                        document.getElementById('nemid_console').value = response;
                    }
                }
            }

            if (event.origin === codefile_origin) {
                console.log(event);

                // Nemid Javascript Client flow

                if (message.command === "SendParameters") {

                    var postMessage = {}

                    postMessage.command = "parameters";
                    postMessage.content = JSON.stringify(window.nemidCFConfig);

                    codefile_iframe.postMessage(JSON.stringify(postMessage), codefile_origin);
                }

                if (message.command === 'changeResponseAndSubmit') {
                    let response = atob(message.content);

                    if (response.match(/^APP/) || response.match(/^SRV/)) {
                        console.error("NEMID ERROR: " + response);
                        return;
                    }

                    if (response.substr(0, 5) === "\<\?xml") {
                        document.getElementById('codefile_console').value = response;
                    }
                }
            }
        }

        if (window.addEventListener) {
            window.addEventListener("message", onNemIDMessage);
        } else if (window.attachEvent) {
            window.attachEvent("onmessage", onNemIDMessage);
        }
    })();


</script>

</body>
</html>
