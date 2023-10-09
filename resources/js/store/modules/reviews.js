const state = () => ({
    list: []
})

const getters = {
    reviews: state => {
        return state.list
    }
}

const actions = {
    getReviews ({ commit }, id) {
        axios
        .get(`/api/products/${id}/reviews`)
        .then(response => {
            commit('setReviews', response.data)
        })
    },
    
    submitReview ({ state, commit }, payload) {
        axios
        .post('/api/products/reviews/submit', payload)
        .then(response => {
            state.list.unshift(response.data.data)
        })
    }
}

const mutations = {
    setReviews (state, payload) {
        state.list = payload.data
    }
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}