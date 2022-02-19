jQuery(function($){
    // alert("mencoba alert ? yakin nich ?");
    // console.log("ini var id nya : "+myAjax.vari_id);
    // console.log("ini prod id nya : "+myAjax.prod_id);
    // console.log("ini qty nya : "+myAjax.qty);
    // console.log("ini permalink : "+myAjax.perma_link);

    //

    if( $('body.single').length && $('body.single-product').length ){
        
        $(".woocommerce-error").append('<a class="button kosongcart">Ya, Ganti Cart Saya<i class="fa fa-trash" aria-hidden="true"></i></a>');
        // $(".woocommerce-error").append('<button id="aliatcbtnerror" class="float-center submit-button" >Kosongkan Cart</button>');
    }
    // $("#aliatcbtnerror").click(function(){

    //     var r = confirm("Yakin Kosongkan cart ?");
    //     if (r == true) {
    //         // console.log("abis pilih ya");
    //         $.ajax({
    //             url:myAjax.ajaxurl,
    //             type:'GET',
    //             data:'action=make_empty_ali_cart',
    //             success:function(results)
    //             {
    //                 if(results!="error")
    //                 {
    //                     // $('#cobasub2').css('display', 'block');
    //                     // alert("berhasil empty bro !");
                        
    //                     location.replace(location.href);
    //                     // location.reload();
    //                 }
    //                 else
    //                 {
    //                     // alert("gagal empty bro !");
    //                 }
    //             },
    //             error: function(error) {
                    
    //             }
    //         });
    //     } else {
    //         // console.log("abis - dan cancel");                     
    //     }
        
    // });

    $(".button.kosongcart").click(function(){

        var r = confirm("Yakin Kosongkan cart dan menambahkan barang ini ke cart anda ?");
        if (r == true) {
            // console.log("abis pilih ya");
            $.ajax({
                url:myAjax.ajaxurl,
                type:'POST',
                data:'action=make_empty_ali_cart&vari_id='+myAjax.vari_id+'&prod_id='+myAjax.prod_id+'&qty='+myAjax.qty,
                success:function(results)
                {
                    if(results!="error")
                    {
                        // $('#cobasub2').css('display', 'block');
                        // alert("berhasil empty bro !");
                        
                        // location.href = "https://google.com";
                        // console.log("link redirect : "+myAjax.perma_link);
                        $(location).attr('href',myAjax.perma_link);
                        // location.replace(location.href);
                        
                        // console.log("ini hasil : "+results);

                        // console.log("sudah dihapus coy !");
                        // location.reload();
                    }
                    else
                    {
                        // alert("gagal empty bro !");
                    }
                },
                error: function(error) {
                    
                }
            });
        } else {
            // console.log("abis - dan cancel");                     
        }
        
    });
});