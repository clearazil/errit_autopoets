/*

    Template Name: DotBike - Bicycle 
    Template URI: ------------------
    Description: This is E-commerce Html Template 
    Author: Dot_themes
    Author URI: http://dotthemes.com/
    Version: 1.0
*/
/*===================================
    js INDEX
=====================================
	01. jQuery MeanMenu
    02. Active hover function
    03. stickey menu
    04. wow js active
    05. scrollUp jquery active
    06. Preloader
    07. MatchHeight activation
    08. Nivo slider activation
    09. Carousel slider
    11. price-slider active
    12. zoom 
    
======================================*/

(function($) {
    "use strict";
   /*-------------------------------------------
    	01. jQuery MeanMenu
    --------------------------------------------- */
    jQuery('nav#dropdown').meanmenu();

    
    /*---------------------------
        02. Active hover function
    ----------------------------*/
    $('body').on('mouseenter', '.currency li, .language li, .meni-cart', function() {
        $(this).find(".currency-menu, .language-menu, .cart-area").stop(true).slideDown();
    });

    $('body').on('mouseleave', '.currency li, .language li, .meni-cart', function() {
        $(this).find(".currency-menu, .language-menu, .cart-area").stop(true).slideUp();
    });

	/*----------------------------
       03. stickey menu
    ----------------------------*/
    $(window).on('scroll',function() {    
	   var scroll = $(window).scrollTop();
	   if (scroll < 265) {
		$(".sticky-header").removeClass("sticky");
	   }else{
		$(".sticky-header").addClass("sticky");
	   }
	});
    
   /*------------------
    	04. wow js active
    ---------------- */
    new WOW().init();
   /*------------
    	05. scrollUp jquery active
    ------------- */
    $.scrollUp({
        scrollText: '<i class="fa fa-angle-up"></i>',
        easingType: 'linear',
        scrollSpeed: 900,
        animation: 'fade'
    });
    /*---------
	    06. Preloader
	------------------------*/
		/*$(window).on('load', function() {
			$(".preloader").fadeOut("slow");;
		});
        */
    /*----------------------
		07. MatchHeight activation
	----------------------*/
    $('.single-product').matchHeight();
    
    /*----------------------
		08. Nivo slider activation
	----------------------*/
   $('#mainSlider').nivoSlider({
        directionNav: false,
        controlNavThumbs: false,
        animSpeed: 1000,
        slices: 10,
        pauseTime: 5000,
        pauseOnHover: true,
        controlNav: true,
        manualAdvance: false,
        prevText: 'p', 
        nextText: 'n'
    });    
    
    /*--------------------------
      09. Carousel slider
    ---------------------------- */	
    $(".brand-list").owlCarousel({
      autoPlay: false, 
	  slideSpeed:2000,
	  pagination:false,
	  navigation:false,	  
      items : 5,
	  /* transitionStyle : "fade", */    /* [This code for animation ] */
	  navigationText:["<i class='fa fa-angle-left'></i>","<i class='fa fa-angle-right'></i>"],
      itemsDesktop : [1199,4],
	  itemsDesktopSmall : [991,4],
	  itemsTablet: [768,3],
	  itemsMobile : [479,1]
    });
    $(".banner-list").owlCarousel({
      autoPlay: false, 
	  slideSpeed:2000,
	  pagination:false,
	  navigation:true,	  
      items : 2,
	  navigationText:["<i class='fa fa-angle-left'></i>","<i class='fa fa-angle-right'></i>"],
      itemsDesktop : [1199,2],
	  itemsDesktopSmall : [991,1],
	  itemsTablet: [768,1],
	  itemsMobile : [479,1]
    });
    $(".accessories-list").owlCarousel({
      autoPlay: false, 
	  slideSpeed:2000,
	  pagination:false,
	  navigation:true,	  
      items : 3,
	  navigationText:["<i class='fa fa-angle-left'></i>","<i class='fa fa-angle-right'></i>"],
      itemsDesktop : [1199,3],
	  itemsDesktopSmall : [991,2],
	  itemsTablet: [768,1],
	  itemsMobile : [479,1]
    });
    $(".single-product-page-list").owlCarousel({
      autoPlay: false, 
	  slideSpeed:2000,
	  pagination:false,
	  navigation: true,	  
      items : 1,
	  navigationText:["<i class='fa fa-angle-left'></i>","<i class='fa fa-angle-right'></i>"],
      itemsDesktop : [1199,1],
	  itemsDesktopSmall : [991,1],
	  itemsTablet: [768,1],
	  itemsMobile : [479,1]
    });
    $(".s-tab-zoom").owlCarousel({
      autoPlay: false, 
	  slideSpeed:2000,
	  pagination:false,
	  navigation: false,	  
      items : 4,
	  navigationText:["<i class='fa fa-angle-left'></i>","<i class='fa fa-angle-right'></i>"],
      itemsDesktop : [1199,4],
	  itemsDesktopSmall : [991,3],
	  itemsTablet: [768,3],
	  itemsMobile : [479,2]
    });

    $(".accordion-active").collapse({
		accordion:true,
	  open: function() {
		this.slideDown(550);
	  },
	  close: function() {
		this.slideUp(550);
	  }		
	});	
    $(".another-accordion").collapse({
		accordion:true,
	  open: function() {
		this.slideDown(550);
	  },
	  close: function() {
		this.slideUp(550);
	  }		
	});	
    
    
    /*----------------------------
      10. cart-plus-minus-button
    ------------------------------ */
    $(".cart-plus-minus")
    $(".qtybutton").on("click", function() {
        var $button = $(this);
        var oldValue = $button.parent().find("input").val();
        if ($button.text() == "+") {
            var newVal = parseFloat(oldValue) + 1;
        } else {
            // Don't allow decrementing below zero
            if (oldValue > 0) {
                var newVal = parseFloat(oldValue) - 1;
            } else {
                newVal = 0;
            }
        }
        $button.parent().find("input").val(newVal);
    });
    
    /*----------------------------
      11. price-slider active
    -----------------------------*/  
    $( "#price-slider" ).slider({
       range: true,
       min: 0,
       max: 1169,
       values: [ 200, 969 ],
       slide: function( event, ui ) {
        $( "#min-price" ).val('$' + ui.values[ 0 ] );
        $( "#max-price" ).val('$' + ui.values[ 1 ] );
       }
      });
      $( "#min-price" ).val('$' + $( "#price-slider" ).slider( "values", 0 ));   
      $( "#max-price" ).val('$' + $( "#price-slider" ).slider( "values", 1 )); 
    
    
    /*-------------------------------------------
	elevateZoom
	-------------------------------------------- */	
	$("#zoom1").ezPlus({
		gallery:'gallery_01', 
		cursor: 'crosshair', 
		galleryActiveClass: "active", 
	});


    
    /*--------------------
       11. You tube video active
    -------------------------*/
    $(".youtube-bg").YTPlayer({
        videoURL:"https://www.youtube.com/watch?v=sZE8tJnTHhw",
        containment:'.youtube-bg',
        mute:true,
        loop:true,
        showControls: true
        
    });
    
    /*
     * Delete confirm
     */
    $(document).on('submit', '.delete', function() {
        event.preventDefault();

      var choice = confirm(this.getAttribute('data-confirm'));

      if (choice) {
        this.submit();
      }
    });
})(jQuery);

    /*
     * A change event on the categories checkboxes with a timeout. 
     * After 800 miliseconds the form is submitted, unless the form
     * changes again.
     */
    var submitForm;

    $(document).on('change', '.categories-select', function() {
        clearTimeout(submitForm);

        submitForm = setTimeout(function() {
            $('.categories-form').submit();
        }, 800);
    });

    /*
     * Change the shopping cart dropdown and the button that was clicked on if a product 
     * is added to the cart. Then show the modal.
     */
    $('body').on('click', '.add_cart, .added-to-cart, .add-to-cart', function () {
        event.preventDefault();

        var target = $(this).attr('data-target');

        // check if the product hasn't been added to the cart yet
        if (target === '#added-to-cart-modal') {
            var productId = $(this).attr('data-product-id');
            var quantity = $('#product-quantity').val();

            if (typeof quantity === 'undefined') {
                quantity = 1;
            }
            var url = Routing.generate('cart_add', {'id': productId, 'amount': quantity});

            // add the product to the cart and update the cart dropdown
            $.ajax({
                url: url,
                dataType: 'html',
            }).success(function (data) {
                $('.shopping-cart').replaceWith(data);
            });

            var button = $('.product-' + productId);

            // change the button's look to show the product is in the cart
            button.attr('class', 'added-to-cart in-cart-product-' + productId);

            // change the buttons target modal
            button.attr('data-target', '#already-in-cart-modal');
        
            // change the product name and description inside the modal
            var productName = $(this).attr('data-product');
            var productDescription = $(this).attr('data-description');

            $('#modal-product-name').text(productName);
            $('#modal-product-description').text(productDescription);

            // change the glyphicon into a plus sign
            button.find('span').attr('class', 'glyphicon glyphicon-ok');

            $('#product-quantity').prop('disabled', true);
        }

        $(target).modal();
    });


    // User clicks delete button in the dropdown menu
    $('body').on('click', '.cart-del', function() {
        var $this = $(this);
        var productId = $(this).attr('data-product-id');

        var url = Routing.generate('cart_remove', {'id': productId});

        $.ajax({
            url: url,
            dataType: 'json',
        }).success(function (data) {
            if (data.cart_empty) {
                // if removing the product results in an empty cart, slide the dropdown up
                $('.meni-cart').find(".currency-menu, .language-menu, .cart-area").stop(true).slideUp();
                
                // display an empty cart after sliding animation has finished
                setTimeout(function() {
                    $('.shopping-cart').replaceWith(data.cart_dropdown);
                }, 500);
            } else {
                // add the animate class to start animating this li element up and away
                $this.parent().addClass('animate');

                // the slide down sets a fixed height. Setting it back to auto to prevent 
                // leaving a gap when deleting this product's li element
                $('.cart-area').css({height: 'auto'});

                // update the product count and price total
                $('.cart-product-amount').text(data.cart_product_count);
                $('.cart-total-price').text(data.cart_total_price);
            }

            // change all buttons for this product to show that this product is not in the cart
            var button = $('.in-cart-product-' + productId);
            button.attr('class', 'add-to-cart add_cart product-' + productId);
            button.find('span').attr('class', 'glyphicon glyphicon-plus');
            button.attr('data-target', '#added-to-cart-modal');

            // enable the quantity input on the single product page
            $('#product-quantity').prop('disabled', false);
        });
    });

    // remove the product's li element when the animation has finished
    $('body').on('transitionend', '.animate', function(event) {
        $(event.target).remove();
    });


