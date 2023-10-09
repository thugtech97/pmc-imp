<template>
    <form>
        <h4>Leave a Review</h4>

        <div class="form-group">
            <star-rating 
                v-model="form.rating"
                :star-size="25"
            ></star-rating>
        </div>
        
        <div class="form-group">
            <textarea v-model="form.comment" class="form-control" placeholder="Leave a comment..."></textarea>
        </div>

        <button type="button" class="btn btn-submit btn-primary" @click="submitReview(form)">Submit</button>
    </form>
</template>
<script>
    import StarRating from 'vue-star-rating'
    import { mapActions } from 'vuex'

    export default {
        props: ['product', 'user'],
        components: {
            StarRating
        },
        data() {
            return {
                form: {
                    rating: 0,
                    comment: '',
                    productId: this.product.id,
                    userId: this.user.id
                },
            }
        },
        methods: {
            ...mapActions('reviews', {
                submit: 'submitReview'
            }),

            submitReview(form) {
                this.submit(form)
                /*this.$nextTick(() => {
                    this.$refs.comment[0].classList.add('new-comment')
                })*/
            }
        }
    }
</script>