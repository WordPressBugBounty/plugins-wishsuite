;(function($){
"use strict";

    // Active settigns menu item
    if ( typeof WishSuite.is_settings != "undefined" && WishSuite.is_settings == 1 ){
        $('.toplevel_page_wishsuite .wp-first-item').addClass('current');
    }

    // Save value
    wishsuiteConditionField( WishSuite.option_data['btn_icon_type'], 'custom', '.button_custom_icon' );
    wishsuiteConditionField( WishSuite.option_data['added_btn_icon_type'], 'custom', '.addedbutton_custom_icon' );
    wishsuiteConditionField( WishSuite.option_data['shop_btn_position'], 'use_shortcode', '.depend_shop_btn_position_use_shortcode' );
    wishsuiteConditionField( WishSuite.option_data['shop_btn_position'], 'custom_position', '.depend_shop_btn_position_custom_hook' );
    wishsuiteConditionField( WishSuite.option_data['product_btn_position'], 'use_shortcode', '.depend_product_btn_position_use_shortcode' );
    wishsuiteConditionField( WishSuite.option_data['product_btn_position'], 'custom_position', '.depend_product_btn_position_custom_hook' );
    wishsuiteConditionField( WishSuite.option_data['button_style'], 'custom', '.button_custom_style' );
    wishsuiteConditionField( WishSuite.option_data['table_style'], 'custom', '.table_custom_style' );
    wishsuiteConditionField( WishSuite.option_data['notification_style'], 'custom', '.notification_custom_style' );
    wishsuiteConditionField( WishSuite.option_data['enable_social_share'], 'on', '.depend_social_share_enable' );
    wishsuiteConditionField( WishSuite.option_data['enable_login_limit'], 'on', '.depend_user_login_enable' );
    wishsuiteConditionField( WishSuite.option_data['remove_on_click'], 'on', '.depend_remove_on_click_enable' );
    wishsuiteConditionField( WishSuite.option_data['enable_success_notification'], 'on', '.depend_enable_success_notification' );
    wishsuiteConditionField( WishSuite.option_data['delete_guest_user_wishlist'], 'on', '.depend_delete_guest_user_wishlist' );

    // After Select field change Condition Field
    wishsuiteChangeField( '.button_icon_type select', '.button_custom_icon', 'custom' );
    wishsuiteChangeField( '.addedbutton_icon_type select', '.addedbutton_custom_icon', 'custom' );
    wishsuiteChangeField( '.shop_btn_position select', '.depend_shop_btn_position_use_shortcode', 'use_shortcode' );
    wishsuiteChangeField( '.shop_btn_position select', '.depend_shop_btn_position_custom_hook', 'custom_position' );
    wishsuiteChangeField( '.product_btn_position select', '.depend_product_btn_position_use_shortcode', 'use_shortcode' );
    wishsuiteChangeField( '.product_btn_position select', '.depend_product_btn_position_custom_hook', 'custom_position' );
    wishsuiteChangeField( '.button_style select', '.button_custom_style', 'custom' );
    wishsuiteChangeField( '.table_style select', '.table_custom_style', 'custom' );
    wishsuiteChangeField( '.notification_style select', '.notification_custom_style', 'custom' );
    wishsuiteChangeField( '.enable_social_share .checkbox', '.depend_social_share_enable', 'on', 'radio' );
    wishsuiteChangeField( '.enable_login_limit .checkbox', '.depend_user_login_enable', 'on', 'radio' );
    wishsuiteChangeField( '.remove_on_click .checkbox', '.depend_remove_on_click_enable', 'on', 'radio' );
    wishsuiteChangeField( '.enable_success_notification .checkbox', '.depend_enable_success_notification', 'on', 'radio' );
    wishsuiteChangeField( '.delete_guest_user_wishlist .checkbox', '.depend_delete_guest_user_wishlist', 'on', 'radio' );

    function wishsuiteChangeField( filedselector, selector, condition_value, fieldtype = 'select' ){
        $(filedselector).on('change',function(){
            let change_value = '';

            if( fieldtype === 'radio' ){
                if( $(this).is(":checked") ){
                    change_value = $(this).val();
                }
            }else{
                change_value = $(this).val();
            }

            wishsuiteConditionField( change_value, condition_value, selector );
        });
    }

    // Hide || Show
    function wishsuiteConditionField( value, condition_value, selector ){
        if( value === condition_value ){
            $(selector).show();
        }else{
            $(selector).hide();
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        const tabSection = document.getElementById("wishsuite_wishlist_tabs");
        tabSection.querySelector('form').removeAttribute('action');
    })

    const wishsuite_tab_menu = function( targetContent, target ){
        $('.nav-tab-wrapper').find('a').filter( function(i, a) {
            return target === a.hash;
        }).addClass('nav-tab-active').siblings().removeClass('nav-tab-active');
        $("html, body").animate({ scrollTop: 0 }, "fast");
    }

    const wishsuite_sidebar_menu = function ( suffix ) {
        let $sidebararea = $( "#toplevel_page_" + suffix ),
            $href = window.location.href,
            $subhref = $href.substring( $href.indexOf( "admin.php" ) );

        $sidebararea.on("click", "a", function (e) {
            const $this = $(this);
            $("ul.wp-submenu li", $sidebararea).removeClass("current"), $this.hasClass("wp-has-submenu") ? $("li.wp-first-item", $sidebararea).addClass("current") : $this.parents("li").addClass("current");

            const $terget = $this.attr("href"),
            $tergetmenu = $terget.substring( $terget.lastIndexOf("#")+1 ),
            $targetContent = '#'+$tergetmenu;
            wishsuite_tab_menu( $targetContent, '#'+$tergetmenu );
            $( $targetContent ).show().siblings().hide();
            
        }),

        $("ul.wp-submenu a", $sidebararea).each(function (e, $sidebararea) {
            $($sidebararea).attr("href") !== $subhref || $($sidebararea).parent().addClass("current").siblings().removeClass("current");

            const $tergetmenu = $subhref.substring( $subhref.lastIndexOf("#")+1 );

            const $menuhref = $($sidebararea).attr("href"),
                $suburl = $menuhref.substring( $menuhref.lastIndexOf("#")+1 );

            if( $tergetmenu === $suburl ){
                const $targetContent = '#'+$tergetmenu;
                wishsuite_tab_menu( $targetContent, '#'+$tergetmenu );
            }
            
        });

    };
    wishsuite_sidebar_menu('wishsuite');

    // Tab Menu
    function wishsuite_admin_tabs( $tabmenus ){

        $tabmenus.on('click', 'a', function(e){
            const $this = $(this),
                $target = $this.attr('href')

            const $sidebararea = $( "#toplevel_page_wishsuite" );

            window.location.hash = $target;
            $sidebararea.find('.wp-submenu').find('a').filter( function(i, a) {
                return $target === a.hash;
            }).parent().addClass('current').siblings().removeClass('current');
            $("html, body").animate({ scrollTop: 0 }, 0);

        });

    }
    wishsuite_admin_tabs( $(".nav-tab-wrapper") );

})(jQuery);