<template>
    <div class="mt-5">
        <h4>Reviews</h4>

        <div v-if="!items.length" class="text-center">No reviews found.</div>
        <div ref="comment" class="d-flex" v-for="(review, index) in items" :key="index">
            <div class="flex-shrink-0">
                <img :src="review.user_photo" class="rounded-circle" width="50" alt="Sample Image">
            </div>
            <div class="flex-grow-1 ms-3">
                <h5>{{ review.posted_by }} <small class="text-muted"><i>Posted on {{ review.posted_at }}</i></small></h5>
                <star-rating :rating="review.rating" :star-size="25" :read-only="true" :increment="0.01"></star-rating>
                <p class="pt-3">{{ review.comment }}</p>
            </div>
        </div>

        <jw-pagination 
            :items="reviews" 
            @changePage="onChangePage" 
            :pageSize="4"
            :maxPages="5"
        ></jw-pagination>
    </div>
</template>
<script>
    import StarRating from 'vue-star-rating'
    import { mapGetters } from 'vuex'

    export default {
        props: ['product'],
        components: {
            StarRating
        },
        data() {
            return {
                items: []
            }
        },
        computed: {
            ...mapGetters('reviews', [
                'reviews'
            ])
        },
        mounted() {
            this.$store.dispatch('reviews/getReviews', this.product.id)
        },
        methods: {
            onChangePage(pageOfItems) {
                // update page of items
                this.items = pageOfItems;
            }
        }
    }
</script>
<style scoped>
@keyframes append-animate {
	from {
		transform: scale(0);
		opacity: 0;
	}
	to {
		transform: scale(1);
		opacity: 1;	
	}
}

/* animate new box */
.new-comment {
	animation: append-animate .3s linear;
}
</style>