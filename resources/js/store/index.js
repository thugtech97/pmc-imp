import Vue from "vue"
import Vuex from "vuex"
import products from './modules/products'
import reviews from './modules/reviews'
 
Vue.use(Vuex)
 
export default new Vuex.Store({
    modules: {
        products,
        reviews
    }
});