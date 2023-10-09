const state = () => ({
    list: [],
    categories: [],
})

const getters = {
    categories: state => {
        return state.categories
    },
    filteredProducts: state => (filter) => {
        let productList = state.list
        
        if (filter.categories.length > 0) {
            const filtered = productList.filter(item => filter.categories.includes(item.category_id))
            productList = filtered
        }
        if (filter.query != '') {
            const search = filter.query.trim().toLowerCase()

            productList = productList.filter(item => item.name.toLowerCase().indexOf(search) > -1)
        }
        
        return productList
    }
}

const actions = {
    getProducts ({ commit }) {
        axios
        .get('/api/products/list')
        .then(response => {
            commit('setProducts', response.data)
        })
    },
    getCategories ({ commit }) {
        axios
        .get('/api/products/categories')
        .then(response => {
            commit('setCategories', response.data)
        })
    },
}

const mutations = {
    setProducts (state, payload) {
        state.list = payload.data
    },
    setCategories (state, payload) {
        state.categories = payload.data
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}