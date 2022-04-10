<template>
    <div>
        <slot name="loader" v-if="!ready && showLoader">
            <div class="loader" :style="{width: width, height: height}">Loading...</div>
        </slot>
    <iframe
        v-show="ready"
        :id="id"
        :src="src"
        :width="width"
        :height="height"
    ></iframe>
    </div>
</template>

<script>
export default {
    name: "NemidSignature",
    data: function() {
        return {
            ready: false
        }
    },
    props: {
        'classObject': {
            type: String,
            default: 'nemid-javascript'
        },
        'height': {
            type: String,
            default: '255px'
        },
        'id': {
            type: String,
            default: 'nemid_iframe'
        },
        'src': {
            type: String,
            default: ''
        },
        'width': {
            type: String,
            default: '205px'
        },
        'config': {
            type: Object,
        },
        'showLoader': {
            type: Boolean,
            default: false
        }
    },
    methods: {
        load: function (event) {
            let message;
            let domElement = document.getElementById(this.id);

            if(!domElement)
                return;

            let domOrigin = (new URL(domElement.src)).origin;

            try {
                message = JSON.parse(event.data);
            } catch (e) {
                return;
                /*ignore not JSON */
            }

            if (event.origin !== domOrigin) {
                return;
            }

            console.log(message.command);

            if (message.command === 'SendParameters') {
                let postMessage = {
                    command: "parameters",
                    content: JSON.stringify(this.config)
                }

                domElement.contentWindow.postMessage(JSON.stringify(postMessage), domOrigin);

                this.ready = true;

                return;
            }

            if (message.command === 'changeResponseAndSubmit') {
                let response = atob(message.content);

                if (response.match(/^APP/) || response.match(/^SRV/)) {
                    this.$emit('error', "NEMID ERROR: " + response);

                    return;
                }

                if (response.substr(0, 5) === "<?xml") {
                    this.$emit('success', message.content);
                }

                this.ready = false;
            }

            if(message.command === 'logonCancel') {
                this.$emit('cancel');
            }
        }
    },
    mounted() {
        this.load();

        if (window.addEventListener) {
            window.addEventListener("message", this.load);
        } else if (window.attachEvent) {
            window.attachEvent("onmessage", this.load);
        }
    },
    beforeUnmount() {
        if (window.removeEventListener) {
            window.removeEventListener("message", this.load);
        } else if (window.detachEvent) {
            window.detachEvent("onmessage", this.load);
        }
    }
}
</script>

<style lang="css" scoped>
.loader{
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
