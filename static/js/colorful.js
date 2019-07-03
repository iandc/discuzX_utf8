// JavaScript Document
//杩欎釜鍙槸璁╀粬濂界湅涓€鐐硅€屽凡锛屽苟娌℃湁浠€涔堝嵉鐢�
Element.prototype.colorfulBg=function(){
function changeColor(e){ 
if (e.style.backgroundColor!=e.previousElementSibling.style.backgroundColor){ 
return; 
}else{ 
    var rd = parseInt(Math.random()*colors.length);
    e.style.backgroundColor=colors[rd];
return changeColor(e); 
} 
} 
    var colors=["#69D2E7","#A7DBD8","#E0E4CC","#F38630","#FA6900","#C02942","#53777A"];
    var rd = parseInt(Math.random()*colors.length);
    this.style.backgroundColor=colors[rd];
    if(this.previousElementSibling){
    changeColor(this)
    }
    }