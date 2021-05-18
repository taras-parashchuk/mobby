<template>
    <div v-if="categoriesGroups.length" :class="'moduleCategories' + (isHome ? '' : ' moduleCategories--drop')">
        <div :class="'moduleCategories__header' + (isHome ? '' : ' js-menu-open moduleCategories__header--drop')">
            {{$root.trans['common.text.category']}}
        </div>
        <div :class="'moduleCategories__table' + (isHome ? '': ' js-menu-container moduleCategories__table--hidden')">
            <div :class="'moduleCategories__content js-categories-menu' + (isHome ? '' : ' moduleCategories__content--drop') + (depth > 0 ? ' moduleCategories__content--hidden' : '')"
                 v-for="(categoriesGroup, depth) in categoriesGroups"
                 :data-lvl="depth">
                <div class="moduleCategories__inner js-categories-menu-inner">
                    <div v-for="category in categoriesGroup"
                         :class="'moduleCategories__item moduleCategoriesItem js-menu-item ' + (category.descendants_count > 0 ? 'moduleCategories__item--hasChildren js-open-drop-menu': '')"
                         :data-lvl="depth"
                         :data-parent="category.parent_id"
                         :data-id="category.id">
                        <a :href="category.href"
                           :class="'moduleCategoriesItem__link' + (category.descendants_count > 0 ? ' moduleCategoriesItem__link--hasChildren':'')">
                            {{category.translate.name}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "menu-vertical",
        props: ['config', 'isHome', 'categories'],
        data() {
            return {
                categoriesGroups: []
            };
        },
        created() {

            this.categoriesGroups = JSON.parse(this.categories);

            this.init();

        },
        mounted() {

        },
        methods: {
            init() {

                this.$set(this.$root, 'menuCategoriesGroups', this.categoriesGroups);

                _.debounce(function () {

                    $('.js-categories-menu-inner').mCustomScrollbar();

                    let $menu = $('.moduleCategories__table');

                    $('.js-menu-open').on('click', function () {
                        if ($menu.hasClass('moduleCategories__table--hidden')) {
                            $menu
                                .removeClass('moduleCategories__table--hidden')
                                .addClass('moduleCategories__table--show');
                        } else {
                            $menu
                                .removeClass('moduleCategories__table--show')
                                .addClass('moduleCategories__table--hidden');
                        }
                    });


                    $('body')
                        .on('mouseenter', '.js-menu-item', function (e) {
                            e.preventDefault();
                            e.stopPropagation();

                            let $self = $(this);
                            let hasDownMenu = 0;
                            let i = 0;
                            let $next_menus = $(this).closest('.js-categories-menu').nextAll('.js-categories-menu');
                            $next_menus.find('.js-menu-item').hide();
                            $next_menus
                                .addClass('moduleCategories__content--hidden')
                                .removeClass('moduleCategories__content--show');

                            $(this).siblings('.js-menu-item').removeClass('moduleCategoriesItem--active');
                            $(this).addClass('moduleCategoriesItem--active');

                            $next_menus.find('.js-menu-item').each(function () {

                                if ($(this).data('parent') == $self.data('id')) {

                                    if (i == 0) {

                                        hasDownMenu = 1;
                                        $(this).closest('.js-categories-menu')
                                            .removeClass('moduleCategories__content--hidden')
                                            .addClass('moduleCategories__content--show');
                                    }
                                    i++;
                                    $(this).show();
                                }
                            });

                            if (!hasDownMenu) {
                                $(this)
                                    .closest('.js-categories-menu')
                                    .nextAll('.js-categories-menu')
                                    .addClass('moduleCategories__content--hidden')
                                    .removeClass('moduleCategories__content--show');
                            }

                        })
                        .on('mouseout', $menu.find('.js-menu-item'), function (e) {

                            let $prev = $(e.target).closest('.js-categories-menu');

                            let $current = $(e.relatedTarget).closest('.js-categories-menu');

                            if (!$current.hasClass('js-categories-menu')) {
                                $current.closest('js-categories-menu');
                            }

                            if (!$prev.hasClass('js-categories-menu')) {
                                $prev.closest('js-categories-menu');
                            }


                            if ($prev.data('lvl') == $current.data('lvl') && !$current.hasClass('js-categories-menu')) {
                                $(this).closest('.js-categories-menu').nextAll('.js-categories-menu').find('.js-menu-item').hide();

                            } else if ($current.data('lvl') < $prev.data('lvl')) {

                                $current.nextAll('.js-categories-menu').find('.js-menu-item').hide();
                                $current.nextAll('.js-categories-menu')
                                    .addClass('moduleCategories__content--hidden')
                                    .removeClass('moduleCategories__content--show');
                            } else if ($prev.data('lvl') == $current.data('lvl') && $current.hasClass('js-categories-menu')) {
                            } else if ($current.hasClass('js-categories-menu')) {

                                $current.nextAll('.js-categories-menu')
                                    .addClass('moduleCategories__content--hidden')
                                    .removeClass('moduleCategories__content--show')
                                    .find('.js-menu-item').hide();
                            }
                        });

                    $menu.on('mouseenter', '.js-categories-menu', function (e) {

                        let $current = $(e.target);
                        let $prev = $(e.relatedTarget);

                        /* if(!$current.hasClass('js-categories-menu')){
                             $current.closest('js-categories-menu');
                         }

                         if(!$prev.hasClass('js-categories-menu')){
                             $prev.closest('js-categories-menu');
                         }*/

                        if ($current.data('lvl') < $prev.data('lvl')) {


                            $prev.find('.js-menu-item').hide();

                            $prev
                                .addClass('moduleCategories__content--hidden')
                                .removeClass('moduleCategories__content--show');

                            $prev.nextAll('.js-categories-menu').find('.js-menu-item').hide();

                            $prev.nextAll('.js-categories-menu')
                                .addClass('moduleCategories__content--hidden')
                                .removeClass('moduleCategories__content--show');

                        }
                    });

                    $menu.on('mouseleave', function () {
                        $('.js-categories-menu').each(function () {
                            if ($(this).data('lvl') != 0) {
                                $(this)
                                    .addClass('moduleCategories__content--hidden')
                                    .removeClass('moduleCategories__content--show')
                                    .find('.js-menu-item').hide();
                            }
                        });
                    });
                }, 500)();
            }
        }
    }
</script>

<style scoped>

</style>