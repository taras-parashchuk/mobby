<template>
    <div>
        <div class="account">
            <div class="accountTop">
                <h1 class="pageTitle">{{$root.trans['account.text.heading.account']}}</h1>
            </div>
            <div class="l-flex l-flex-top">

                <aside id="columnLeft" class="columnLeft">
                    <div class="moduleAccount">
                        <ul class="list">
                            <li class="list__item">
                                <div class="list__cell list__cell--line"></div>
                                <div class="list__cell">
                                    <router-link :to="{name:'contacts'}" class="list__link">
                                        {{$root.trans['account.menu.contacts']}}
                                    </router-link>
                                </div>
                            </li>
                            <li class="list__item">
                                <div class="list__cell list__cell--line"></div>
                                <div class="list__cell">
                                    <router-link :to="{name:'wishlist'}" class="list__link">
                                        {{$root.trans['account.menu.wishlist']}}
                                    </router-link>
                                </div>
                            </li>
                            <li class="list__item">
                                <div class="list__cell list__cell--line"></div>
                                <div class="list__cell">
                                    <router-link :to="{name: 'orders'}" class="list__link">
                                        {{$root.trans['account.menu.orders']}}
                                    </router-link>
                                </div>
                            </li>
                            <li class="list__item">
                                <div class="list__cell list__cell--line"></div>
                                <div class="list__cell">
                                    <router-link :to="{name:'security'}" class="list__link">
                                        {{$root.trans['account.menu.security']}}
                                    </router-link>
                                </div>
                            </li>
                            <li class="list__item">
                                <div class="list__cell list__cell--line"></div>
                                <div class="list__cell">
                                    <router-link :to="{name:'subscribes'}" class="list__link">
                                        {{$root.trans['account.menu.subscribes']}}
                                    </router-link>
                                </div>
                            </li>
                        </ul>
                        <a :href="$root.routes['logout']" class="btn btn--login btn--second">{{$root.trans['account.button.logout']}}</a>
                    </div>
                </aside>

                <div id="content" class="accountContent">
                    <router-view :account-info="accountInfo" :save-link="$router.currentRoute.path"></router-view>
                </div>
            </div>
        </div>

    </div>

    <!--

    -->
</template>

<script>

    import VueRouter from 'vue-router'

    Vue.use(VueRouter);

    let router = new VueRouter({
        linkActiveClass: 'list__link--active',
        mode: 'history',
        routes: [
            {
                name: 'contacts',
                path: '/account/edit-contacts',
                component: require('./accountEdit').default
            },
            {
                name: 'wishlist',
                path: '/account/wishlist',
                component: require('./accountWishlist').default
            },
            {
                name: 'orders',
                path: '/account/orders',
                component: require('./accountOrders').default
            },
            {
                name: 'security',
                path: '/account/security',
                component: require('./accountSecurity').default
            },
            {
                name: 'subscribes',
                path: '/account/subscribes',
                component: require('./accountSubscribes').default
            },
            {path: '/account/orders/:id/', component: require('./accountOrder').default, name: 'order'},
        ]
    });

    export default {
        name: "account",
        router,
        props: ['accountInfo'],
        data() {
            return {}
        },
    }
</script>

<style scoped>

</style>