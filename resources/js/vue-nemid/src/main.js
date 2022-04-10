import Vue from 'vue'
import App from './App.vue'

Vue.config.productionTip = false

window.axios = require('axios/index');

new Vue({
  render: h => h(App),
}).$mount('#app')
