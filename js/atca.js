jQuery(function($){

    if( $('body.single').length && $('body.single-product').length ){
        
        $(".woocommerce-error").append('<a class="button kosongcart">Ya, Ganti Cart Saya<i class="fa fa-trash" aria-hidden="true"></i></a>');
    }

    $(".button.kosongcart").click(function(){

        var r = confirm("Yakin Kosongkan cart dan menambahkan barang ini ke cart anda ?");
        if (r == true) {
           
            $.ajax({
                url:myAjax.ajaxurl,
                type:'POST',
                data:'action=make_empty_ali_cart&vari_id='+myAjax.vari_id+'&prod_id='+myAjax.prod_id+'&qty='+myAjax.qty,
                success:function(results)
                {
                    if(results!="error")
                    {
                        $(location).attr('href',myAjax.perma_link);
                    }
                    else
                    {
                        
                    }
                },
                error: function(error) {
                    
                }
            });
        } else {
                                
        }
        
    });
});