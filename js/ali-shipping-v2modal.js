jQuery(function($){

    $(document).ready(function(){
        $(document.body).on('click', '.wc-shipping-zone-method-settings', function (e) {
            $(document.body).off('wc_backbone_modal_loaded', function(){
                $("#woocommerce_AsiaCom_Shipping_ali_province").select2();
                $("#woocommerce_AsiaCom_Shipping_ali_city").select2();
            });

            var cek_data = $(e.currentTarget).closest('tr').find('.wc-shipping-zone-method-type').text();
            console.log(cek_data);
            if(cek_data.toLowerCase().includes("asiacommerce")==true){

                $("#woocommerce_AsiaCom_Shipping_ali_province").select2();
                $("#woocommerce_AsiaCom_Shipping_ali_city").select2();

                var id_provinsi2 = $(".ali_provi").val();
                var selected_city = $(".ali_citi").val();

                if(id_provinsi2){
                    $.ajax({
                        type: "GET",
                        url: "https://api2.asiacommerce.net/api/v2/cities?page[limit]=1000&filter[province_id][is]="+id_provinsi2,
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        crossDomain: true,
                        dataType: 'json',
                        success: function(responseData, status, xhr) {
                            $(".ali_citi").find('option').remove().end();
                            $(".ali_citi").append("<option value=''>----- Select City -----</option>");
                            $.each(responseData['data'], function(i, value) {
                                if(selected_city==value['attributes']['name']){
                                    $(".ali_citi").append('<option value="'+value['attributes']['name']+'" selected="selected">'+value['attributes']['name']+'</option>');
                                }
                                else{
                                    $(".ali_citi").append('<option value="'+value['attributes']['name']+'">'+value['attributes']['name']+'</option>');
                                }
                            });
                                
                            
                        },
                        error: function(request, status, error) {
                            console.log(request.responseText);
                        }
                    });
                }
            }
        });
        $(document.body).on('change', '#woocommerce_AsiaCom_Shipping_ali_province', function () {
            
            var id_provinsi = $(".ali_provi").val();
            $.ajax({
                type: "GET",
                url: "https://api2.asiacommerce.net/api/v2/cities?page[limit]=1000&filter[province_id][is]="+id_provinsi,
                headers: {
                    'Content-Type': 'application/json'
                },
                crossDomain: true,
                dataType: 'json',
                success: function(responseData, status, xhr) {
                    $(".ali_citi").find('option').remove().end();
                    $(".ali_citi").append("<option value=''>----- Select City -----</option>");
                    $.each(responseData['data'], function(i, value) {
                        $(".ali_citi").append('<option value="'+value['attributes']['name']+'">'+value['attributes']['name']+'</option>');
                    });                   
                },
                error: function(request, status, error) {
                    console.log(request.responseText);
                }
            });
        });
    
    });
});
