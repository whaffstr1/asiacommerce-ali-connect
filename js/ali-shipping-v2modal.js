jQuery(function($){
    // $.fn.modal.Constructor.prototype.enforceFocus = function() {};
    // $("#woocommerce_AsiaCom_Shipping_ali_province").selectWoo();
    // $("#woocommerce_AsiaCom_Shipping_ali_city").selectWoo();
    
    // $("#woocommerce_AsiaCom_Shipping_ali_province").select2({ dropdownParent: $("#wc-backbone-modal-dialog") });
    // $("#woocommerce_AsiaCom_Shipping_ali_city").select2({ dropdownParent: $("#wc-backbone-modal-dialog") });
    // $("#woocommerce_AsiaCom_Shipping_ali_province").select2({ dropdownParent: "#wc-backbone-modal-dialog" });
    // console.log("masuk sini ?");
    // $("#woocommerce_AsiaCom_Shipping_ali_city").select2({ dropdownParent: "#wc-backbone-modal-dialog" });
    // console.log("masuk sini 2?");
    // var id_provinsi = $(".ali_prov").val();
    // var selected_city = $(".ali_cit").val();
    // // console.log('masuk js');
    // // console.log(selected_city);

    // if(id_provinsi){
    //     $.ajax({
    //         type: "GET",
    //         url: "https://api2.asiacommerce.net/api/v2/cities?page[limit]=1000&filter[province_id][is]="+id_provinsi,
    //         headers: {
    //             'Authorization': 'Bearer '+'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
    //             // 'Content-Type: application/json'
    //             // 'Authorization': "OAuth " + sessionId,
    //             'Content-Type': 'application/json'
    //         },
    //         crossDomain: true,
    //         // data: JSON.stringify(accountInfo),
    //         dataType: 'json',
    //         success: function(responseData, status, xhr) {
    //             // console.log(responseData);
    //             // console.log(responseData['data'][0]['id']);
    //             $(".ali_cit").find('option').remove().end();
    //             $(".ali_cit").append("<option value=''>----- Select City -----</option>");
    //             $.each(responseData['data'], function(i, value) {
    //                 // alert(data[i].PageName);
    //                 // console.log(value['id']+" "+value['attributes']['name']);
    //                 console.log(value['attributes']['name']);
    //                 if(selected_city==value['attributes']['name']){
    //                     $(".ali_cit").append('<option value="'+value['attributes']['name']+'" selected="selected">'+value['attributes']['name']+'</option>');
    //                 }
    //                 else{
    //                     $(".ali_cit").append('<option value="'+value['attributes']['name']+'">'+value['attributes']['name']+'</option>');
    //                 }
    //             });
                    
                
    //         },
    //         error: function(request, status, error) {
    //             console.log(request.responseText);
    //         }
    //     });
    // }

    //start here
    $(document).ready(function(){
        $(document.body).on('click', '.wc-shipping-zone-method-settings', function (e) {
            $(document.body).off('wc_backbone_modal_loaded', function(){
                $("#woocommerce_AsiaCom_Shipping_ali_province").select2();
                $("#woocommerce_AsiaCom_Shipping_ali_city").select2();
            });
            // var data = $(".woocommerce-error").text();
            
            
            var cek_data = $(e.currentTarget).closest('tr').find('.wc-shipping-zone-method-type').text();
            console.log(cek_data);
            if(cek_data.toLowerCase().includes("asiacommerce")==true){

                // wc-backbone-modal-dialog
                // $(document.body).on('shown.bs.modal', function(){
                //     var id_provinsi2 = $(".ali_provi").val();
                //     console.log("dataaaa "+id_provinsi2);
                // });
                $("#woocommerce_AsiaCom_Shipping_ali_province").select2();
                $("#woocommerce_AsiaCom_Shipping_ali_city").select2();

                var id_provinsi2 = $(".ali_provi").val();
                var selected_city = $(".ali_citi").val();
                // console.log("data "+id_provinsi2);

                if(id_provinsi2){
                    $.ajax({
                        type: "GET",
                        url: "https://api2.asiacommerce.net/api/v2/cities?page[limit]=1000&filter[province_id][is]="+id_provinsi2,
                        headers: {
                            'Authorization': 'Bearer '+'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                            // 'Content-Type: application/json'
                            // 'Authorization': "OAuth " + sessionId,
                            'Content-Type': 'application/json'
                        },
                        crossDomain: true,
                        // data: JSON.stringify(accountInfo),
                        dataType: 'json',
                        success: function(responseData, status, xhr) {
                            // console.log(responseData);
                            // console.log(responseData['data'][0]['id']);
                            $(".ali_citi").find('option').remove().end();
                            $(".ali_citi").append("<option value=''>----- Select City -----</option>");
                            $.each(responseData['data'], function(i, value) {
                                // alert(data[i].PageName);
                                // console.log(value['id']+" "+value['attributes']['name']);
                                // console.log(value['attributes']['name']);
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

                // $(document.body).on('wc_backbone_modal_loaded', function(){
                //     $("#woocommerce_AsiaCom_Shipping_ali_province").select2();
                //     $("#woocommerce_AsiaCom_Shipping_ali_city").select2();

                    
                // });
            }
            // if ($(e.currentTarget).closest('tr').find('.wc-shipping-zone-method-type').text() === woongkir_params.method_title) {
            //     $(document.body).on('wc_backbone_modal_loaded', function(){
            //         $("#woocommerce_AsiaCom_Shipping_ali_province").select2();
            //     });
            // }
        });

        //woocommerce_AsiaCom_Shipping_ali_city

        // $(document.body).on('click', '#woocommerce_AsiaCom_Shipping_ali_province', function () {
        //     $("#woocommerce_AsiaCom_Shipping_ali_province").select2();
        // });

        // $(document.body).on('click', '#woocommerce_AsiaCom_Shipping_ali_city', function () {
        //     $("#woocommerce_AsiaCom_Shipping_ali_city").select2();
        // });

        // $(document.body).on('change', '#woocommerce_AsiaCom_Shipping_ali_province', function () {
        //     $("#woocommerce_AsiaCom_Shipping_ali_province").select2();
        // });
        $(document.body).on('change', '#woocommerce_AsiaCom_Shipping_ali_province', function () {
            
            var id_provinsi = $(".ali_provi").val();
            // console.log("data : "+"( "+id_provinsi+" )");
            $.ajax({
                type: "GET",
                url: "https://api2.asiacommerce.net/api/v2/cities?page[limit]=1000&filter[province_id][is]="+id_provinsi,
                headers: {
                    'Authorization': 'Bearer '+'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                    // 'Content-Type: application/json'
                    // 'Authorization': "OAuth " + sessionId,
                    'Content-Type': 'application/json'
                },
                crossDomain: true,
                // data: JSON.stringify(accountInfo),
                dataType: 'json',
                success: function(responseData, status, xhr) {
                    // console.log(responseData);
                    // console.log(responseData['data'][0]['id']);
                    $(".ali_citi").find('option').remove().end();
                    $(".ali_citi").append("<option value=''>----- Select City -----</option>");
                    $.each(responseData['data'], function(i, value) {
                        // alert(data[i].PageName);
                        // console.log(value['id']+" "+value['attributes']['name']);
                        $(".ali_citi").append('<option value="'+value['attributes']['name']+'">'+value['attributes']['name']+'</option>');
                    });
                        
                    
                },
                error: function(request, status, error) {
                    console.log(request.responseText);
                }
            });
        });
    
    });
    
    // var inc1=0;

    // $( "tbody" ).on( "click", ".wc-shipping-zone-method-settings", function() {

    //     if($(".wc-backbone-modal-content".length)){
    //         console.log("ada");
    //         if($("body #woocommerce_AsiaCom_Shipping_ali_province".length)){
                
    //             console.log("onok blogggg");
    //             $("body #woocommerce_AsiaCom_Shipping_ali_province").select2({ dropdownParent: "#wc-backbone-modal-dialog" });
    //             // var dataa = $("body").html();
    //             // var dataa = $("#woocommerce_AsiaCom_Shipping_ali_province").html();
    //             // console.log(dataa);
    //             // console.log($("#woocommerce_AsiaCom_Shipping_ali_province").text);
    //         }
    //         else{
    //             console.log("tdk ada field province");
    //         }
    //     }
    //     else
    //     {
    //         console.log("tdk ada");
    //     }

    //     if( $("#woocommerce_AsiaCom_Shipping_ali_province").length){
    //         $("#woocommerce_AsiaCom_Shipping_ali_province").select2();
    //     }
    //     if( $("#woocommerce_AsiaCom_Shipping_ali_city").length){
    //         $("#woocommerce_AsiaCom_Shipping_ali_city").select2();
    //     }
    //     // console.log("halo "+inc1++);
    //     // $("#woocommerce_AsiaCom_Shipping_ali_province").select2({ dropdownParent: "#wc-backbone-modal-dialog" });
    // // console.log("masuk sini ?");
    //     // $("#woocommerce_AsiaCom_Shipping_ali_city").select2({ dropdownParent: "#wc-backbone-modal-dialog" });
    // });
    //end here

    // $(".wc-shipping-zone-method-settings").on('click', function() {
    //     console.log("halo "+inc1++);
    // });
    // $(".ali_prov").on('change', function() {
    //     $(".ali_cit").find('option').remove().end();
    //     id_provinsi = $(".ali_prov").val();
    //     $.ajax({
    //         type: "GET",
    //         url: "https://api2.asiacommerce.net/api/v2/cities?page[limit]=1000&filter[province_id][is]="+id_provinsi,
    //         headers: {
    //             'Authorization': 'Bearer '+'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
    //             // 'Content-Type: application/json'
    //             // 'Authorization': "OAuth " + sessionId,
    //             'Content-Type': 'application/json'
    //         },
    //         crossDomain: true,
    //         // data: JSON.stringify(accountInfo),
    //         dataType: 'json',
    //         success: function(responseData, status, xhr) {
    //             // console.log(responseData);
    //             // console.log(responseData['data'][0]['id']);
    //             $(".ali_cit").append("<option value=''>----- Select City -----</option>");
    //             $.each(responseData['data'], function(i, value) {
    //                 // alert(data[i].PageName);
    //                 // console.log(value['id']+" "+value['attributes']['name']);
    //                 $(".ali_cit").append('<option value="'+value['attributes']['name']+'">'+value['attributes']['name']+'</option>');
    //             });
                    
                
    //         },
    //         error: function(request, status, error) {
    //             console.log(request.responseText);
    //         }
    //     });
    // });
});
