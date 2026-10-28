<template>
    <div class="row gutter-40 col-mb-80">
        <vue-typeahead-bootstrap
            v-model="filter.query"
            :data="filteredProducts"
            :serializer="item => item.name"
            placeholder="Search product..."
        >
            <template slot="suggestion" slot-scope="{ data, htmlText }">
                <div class="d-flex align-items-center">
                <img
                    class="rounded-circle"
                    :src="data.photoPrimary"
                    style="width: 40px; height: 40px; margin-right: 10px" />

                <!-- Note: the v-html binding is used, as htmlText contains
                    the suggestion text highlighted with <strong> tags -->
                <span class="ml-4" v-html="htmlText"></span>
                </div>
            </template>
        </vue-typeahead-bootstrap>

        <div class="postcontent col-lg-9 order-lg-last">
            <div id="shop" class="shop row grid-container gutter-20" data-layout="fitRows">
                <div :class="'product col-md-4 col-sm-6 ' + product.category.name" v-for="(product, index) in items" :key="index">
                    <div class="grid-inner">
                        <div class="product-image">
                            <a href="#"><img :src="product.photoPrimary" alt="Checked Short Dress"></a>
                            <a href="#"><img src="images/shop/dress/1-1.jpg" alt="Checked Short Dress"></a>
                            <div class="sale-flash badge bg-secondary p-2">Out of Stock</div>
                            <div class="bg-overlay">
                                <div class="bg-overlay-content align-items-end justify-content-between" data-hover-animate="fadeIn" data-hover-speed="400">
                                    <a href="#" class="btn btn-dark me-2"><i class="icon-shopping-cart"></i></a>
                                    <a href="include/ajax/shop-item.html" class="btn btn-dark" data-lightbox="ajax"><i class="icon-line-expand"></i></a>
                                </div>
                                <div class="bg-overlay-bg bg-transparent"></div>
                            </div>
                        </div>
                        <div class="product-desc">
                            <div class="product-title"><h3><a :href="'/product-details/' + product.slug">{{ product.name }}</a></h3></div>
                            <div class="product-price"><del>$24.99</del> <ins>$12.49</ins></div>
                            <div class="product-rating">
                                <i class="icon-star3"></i>
                                <i class="icon-star3"></i>
                                <i class="icon-star3"></i>
                                <i class="icon-star3"></i>
                                <i class="icon-star-half-full"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar
        ============================================= -->
        <div class="sidebar col-lg-3">
            <div class="sidebar-widgets-wrap">

                <div class="widget widget-filter-links">

                    <h4>Select Category</h4>
                    <ul class="custom-filter ps-2" data-container="#shop" data-active-class="active-filter">
                        <li class="widget-filter-reset active-filter"><a href="#" data-filter="*">Clear</a></li>
                        <li v-for="(category, index) in categories" :key="index">
                            <a href="#" :data-filter="'.' + category.name">
                                {{ category.name }}
                            </a>
                        </li>
                    </ul>

                </div>

                <div class="widget widget-filter-links">

                    <h4>Sort By</h4>
                    <ul class="shop-sorting ps-2">
                        <li class="widget-filter-reset active-filter"><a href="#" data-sort-by="original-order">Clear</a></li>
                        <li><a href="#" data-sort-by="name">Name</a></li>
                        <li><a href="#" data-sort-by="price_lh">Price: Low to High</a></li>
                        <li><a href="#" data-sort-by="price_hl">Price: High to Low</a></li>
                    </ul>

                </div>

            </div>
        </div><!-- .sidebar end -->
        <hr />
        <div class="row">
            <div class="col-md-3">
                <h4>Categories</h4>
                    <div v-for="(category, index) in categories" :key="index" class="form-check form-check-inline">
                        <input v-model="filter.categories" class="form-check-input" type="checkbox" :id="'inlineCheckbox' + category.id" :value="category.id">
                        <label class="form-check-label" :for="'inlineCheckbox' + category.id">{{ category.name }}</label>
                    </div>
                <hr />
                <input @click="filter = {}" class="btn btn-danger" value="Clear All" style="width: 100%">
            </div>
            
            <div class="col-md-9">
                <Sort></Sort>

                <div class="row">
                    <div class="col-lg-3 col-md-4" v-for="(product, index) in items" :key="index">
                        <div class="portfolio-item">
                            <div class="item-categories">
                                <div class="">
                                    <a :href="'/product-details/' + product.slug" class="d-block h-op-09 op-ts" :style="'background: url(' + product.photoPrimary + ') no-repeat center center; background-size: cover; height: 340px;'">
                                        <h5 class="text-uppercase ls1 bg-white mb-0">{{ product.name }}</h5>
                                        <h6 class="text-uppercase ls1 bg-white mb-0">Best Seller</h6>
                                    </a>
                                </div>
                            </div>
                            <div class="product-desc">
                                <div class="product-title mb-0"><h4 class="mb-0"><a class="fw-medium" :href="'/product-details/' + product.slug">{{ product.name }}</a></h4></div>
                                    <p :style="{ color: checkIfCritical(randStock, product.critical_qty) }">Stocks Available: {{ randStock }}</p>
                                    <input type="hidden" id="product_price" :value="product.price">
                                    <h5 class="product-price fw-normal">₱{{ product.price }}</h5>
                            </div>
                        </div>
                    </div>
                </div>

                <jw-pagination 
                    :items="filteredProducts" 
                    @changePage="onChangePage" 
                    :pageSize="8"
                    :maxPages="4"
                ></jw-pagination>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex'
    import Sort from './Sort.vue'
    import VueTypeaheadBootstrap from 'vue-typeahead-bootstrap'

    export default {
        components: {
            Sort,
            VueTypeaheadBootstrap
        },
        data() {
            return {
                loading: true,
                filter: {
                    categories: [],
                    query: ''
                },
                items: [],
            }
        },
        computed: {
            // mix the getters into computed with object spread operator
            ...mapGetters('products', {
                categories: 'categories',
                products: 'filteredProducts'
            }),

            filteredProducts() {
                return this.products(this.filter)
            },

            randStock() {
                return Math.floor((Math.random() * 10) + 1)
            }
        },
        mounted () {
            this.$store.dispatch('products/getProducts')
            this.$store.dispatch('products/getCategories')
            /*axios
            .get('/api/products/list')
            .then(response => {
                this.products = response.data.data
            })
            .catch(error => {
                console.log(error)
                this.errored = true
            })
            .finally(() => this.loading = false)*/
        },
        methods: {
            onChangePage(pageOfItems) {
                // update page of items
                this.items = pageOfItems;
            },

            checkIfCritical(current_stocks, critical_qty) {                
                if (current_stocks <= critical_qty) {
                    return 'red'
                }
                else {
                    return ''
                }
            }
        }
    }
</script>
<style scoped>
    .pagination {
        float: right;
        margin-top: 20px !important;
    }
</style>