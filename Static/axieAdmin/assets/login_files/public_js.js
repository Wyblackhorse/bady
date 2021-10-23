$(function() {
	// $('nav#menu').mmenu();
    $('.headerSet .headerSetBoxCart').hover(function(){
        $('.cartBox').stop().slideToggle()
        $('.navMarket').stop().fadeIn() 
    },function(){
        $('.cartBox').stop().slideToggle()
        $('.navMarket').stop().fadeOut() 
    })
    $('.tabs01 .items').click(function(){
        $(this).addClass('active').siblings().removeClass('active')
        $('.tabs01Content .index_box2').eq($(this).index()).show().siblings('.tabs01Content .index_box2').hide()
    })
    $('.tabs02 .items').click(function(){
        $(this).addClass('active').siblings().removeClass('active')
        $('.tabs02Content .index_box2').eq($(this).index()).show().siblings('.tabs02Content .index_box2').hide()
    })
    $('.sidebar .items').click(function(){
        if($(this).hasClass('active')){
            $(this).removeClass('active')
        }else{
            $('.sidebar .items').removeClass('active')
            $(this).addClass('active')
        }
    })
    $('.sidebar2').click(function(){
        $('.sidebar2').removeClass('active')
        $('.sidebar1').addClass('active')
    })
    $('.sidebar1 .opens').click(function(){
        $('.sidebar .items').removeClass('active')
        $('.sidebar1').removeClass('active')
        $('.sidebar2').addClass('active')
    })
    $('.navs > .items').hover(function(){
    	var indexs = $(this).find('.navDown').length
    	if(indexs!='0'){
    		$(this).find('.navDown').stop().slideToggle()
    		$('.navMarket').stop().fadeIn()	
    	}
    },function(){
    	$(this).find('.navDown').stop().slideToggle()
    	$('.navMarket').stop().fadeOut()
    })
    $('.headerSetBox').hover(function(){
    	var indexs = $(this).find('.searchBox').length
    	if(indexs!='0'){
    		$(this).find('.searchBox').stop().slideToggle()
    		$('.navMarket').stop().fadeIn()	
    	}
    },function(){
    	$(this).find('.searchBox').stop().slideToggle()
    	$('.navMarket').stop().fadeOut()
    })
    $('.productTab01 > .items').click(function() {
        $(this).addClass('active').siblings().removeClass('active')
        $('.productContentTab > .items').eq($(this).index()).show().siblings().hide()
    });
    $('.productTab02 .items').click(function() {
        $(this).addClass('active').siblings().removeClass('active')
        $(this).parent().parent().siblings('.productList').hide()
        $(this).parent().parent().siblings('.productList').eq($(this).index()).show()
    });
    // $('.addressList > .items').click(function() {
    //     $(this).addClass('active').siblings().removeClass('active')
    // })
    $('.pdetailTabs > .items').click(function() {
        $(this).addClass('active').siblings().removeClass('active')
        $('.pdetailTabContent > .items').eq($(this).index()).show().siblings().hide()
    });
	
	$(window).scroll(function(){
		if($(this).scrollTop() > 100){
			$('.productDetailNew02').addClass('active');
		}else{
			$('.productDetailNew02').removeClass('active')
		}
	});
	
})
function openProductDetailNew(el){
	if($(el).hasClass('active')){
		$(el).removeClass('active')
		$(el).find('.secondNav').stop().slideUp()
	}else{
		$(el).addClass('active')
		$(el).find('.secondNav').stop().slideDown()
	}
}
function openfreeBox01(){
    $('.freeBox01').show()
}
function openfreeBox02(){
    $('.freeBox02').show()
}
function openfreeBox03(){
    $('.freeBox03').show()
}
function closefreeBox(){
    $('.freeBox').hide()
}