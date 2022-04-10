<template>
    <div id="app">
        <nemid-signature
            id="codefile_iframe"
            v-if="cfConfig"
            width="300px"
            height="200px"
            :src="'https://codefileclient.pp.danid.dk/?t=' + Date.now()"
            :config="cfConfig"
            @success="success"
            @error="error"
        />
        <nemid-signature
            id="nemid_iframe"
            v-if="jsConfig"
            :src="'https://appletk.danid.dk/launcher/std/' + Date.now()"
            :config="jsConfig"
            @success="success"
            @error="error"
        />
    </div>
</template>

<script>

import NemidSignature from "./components/NemidSignature";

export default {
    name: 'App',
    data: function () {
        return {
            jsConfig: {},
            cfConfig: {},
        }
    },
    components: {
        NemidSignature
    },
    methods: {
        load: function () {
            window.axios.get('/nemid/javascript').then((response) => this.jsConfig = response.data);
            window.axios.get('/nemid/codefile').then((response) => this.cfConfig = response.data);
        },
        success() {
            alert('success');
        },
        error(message) {
            alert(message)
        }
    },
    mounted() {
        this.load();
    }
}
</script>

<style>

</style>
