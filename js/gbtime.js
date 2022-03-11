jQuery( document ).ready( function() {  
    var countDownDate = new Date(myAjax.waktu).getTime();

    var x = setInterval(function() {

        var now = new Date().getTime();
            
        var distance = countDownDate - now;     
            
        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);
          
        if (distance < 0) {
            clearInterval(x);
            document.getElementById("saleend").innerHTML = "(Promo Ended! You cannot buy anymore!)";
        }
    }, 1000);        
});