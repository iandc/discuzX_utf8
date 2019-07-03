




//轮播
var mySwiper = new Swiper('.swiper-container',{
    autoplay:{
        disableOnInteraction:false,
        delay:3000,
    },
    loop:true,
    pagination:{
        el:'.swiper-pagination',
        clickable :true,
    },
    // navigation:{
    //     nextEl:'.swiper-button-next',
    //     prevEl:'.swiper-button-prev',
    // },
});


$(function(){
    //demo示例一到四 通过lass调取，一句可以搞定，用于页面中可能有多个导航的情况
    $('.wrapper').navbarscroll();
});