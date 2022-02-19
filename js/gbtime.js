jQuery( document ).ready( function() {  
    // var countDownDate = <?php echo $sale_date; ?> * 1000;
    // $.ajaxSetup({ cache: false });
    

    var countDownDate = new Date(myAjax.waktu).getTime();

    
    // Update the count down every 1 second
    var x = setInterval(function() {

        // init();
        // $.ajaxSetup({ cache: false });
        // Get today's date and time
        var now = new Date().getTime();
            
        // Find the distance between now and the count down date
        var distance = countDownDate - now;     
            
        // Time calculations for days, hours, minutes and seconds
        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
        // Output the result in an element with id="saleend"
        // document.getElementById("saleend").innerHTML ="Time left : " + days + "d " + hours + "h " + minutes + "m " + seconds + "s ";
            
        // If the count down is over, write some text 
        if (distance < 0) {
            clearInterval(x);
            document.getElementById("saleend").innerHTML = "(Promo Ended! You cannot buy anymore!)";
        }
    }, 1000);        
});