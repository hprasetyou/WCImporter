define(['jquery',"mage/url","Hprasetyou_WCImporter/lib/datatables/datatables.min"], function($,urlBuilder){
    'use strict'

    return function(config, element){
      const createProduct = function(data){
        return $.ajax({
            url:config.createUrl,
            showLoader: true,
            data
          })
      }
      $(document).ready(function() {
        const datatable = $(config.tableEl).DataTable({
          select: true,
          pageLength: 10,
          columns: [
            {
                orderable: false,
                className: 'product-id',
                data: "id"
            },
            { "data": "name" },
            { "data": "price" },
            { "data": "sku" },
            { "data": "date_created" }
          ]
        })
        datatable.getSelected = function(){
          return this.rows('.selected').data()
        }
        $(`${config.tableEl} tbody`).on( 'click', 'tr', function () {
            $(this).toggleClass('selected')
            if(datatable.getSelected().length > 0){
              $(config.importTrigger).show()
            }
        })
        let uncheck = true
        $(config.tableSelectAll).click(function(){
          uncheck = !uncheck
          datatable.rows().every(function() {
            this.nodes().to$().toggleClass('selected', !uncheck)
          })
        })
        $(config.fetchTriggerEl).click(function(){
           $.ajax({
             url: config.fetchUrl,
             showLoader: true,
             data: {form_key: window.FORM_KEY},
           }).then(function(output){
              datatable.clear();
              datatable.rows.add(output.data);
              datatable.draw();
           })
         })

         function createProductsByIndex(data, i = 0){
           console.log(i,data.length);
           if(i < data.length){
             createProduct(data[i]).then(function(o){
               console.log(o);
               createProductsByIndex(data, i + 1)
             })
           }
         }

         $(config.importTrigger).click(function(){
           createProductsByIndex(datatable.getSelected())
         })
      })
    }
})
