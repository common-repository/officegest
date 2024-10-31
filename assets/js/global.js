var artigos_selected = [];
var pecas_usadas_selected = [];
var ecoauto_categories_selected = [];
var ecoauto_componentes_selected = [];
var artigos = null;
var pecas_usadas = null;
var ecoauto_categories = null;
var ecoauto_componentes = null;
jQuery(document).ready(function ($) {
    if ( $( "#billing_country" ).length ) {
        $("#billing_country").select2().on("select2:select", function (e) {
                var selected_element = $(e.currentTarget);
                var select_val = selected_element.val();
                if (select_val != 'PT'){
                    if ( $( "#billing_nif" ).length ) {
                        $("#billing_nif").parent().parent().hide();
                    }
                }
                else{
                    $("#billing_nif").parent().parent().show();
                }
        });
        $("#billing_country").trigger("select2:select");
    }
    if ($("#download_log_file").length){
        $("#download_log_file").on('click',function (e) {
            console.log($(this));
            var link = $(this).data('link');
            window.open(link,'_blank');
        });
    }
    if ( $("#ecoauto_doactionfilter").length ) {
        var data_ = {
            'action': 'updateecoautotable',
            'category': $("#article_category").val(),
            'brand': $("#articles_brand").val(),
            'part_status': $("#part_status").val(),
            'part_types': $("#part_types").val(),
            'apenas_nao_integrados':$("#apenas_nao_integrados").val(),
            'display_table': 0
        };
        pecas_usadas = $('#pecas_usadas').DataTable({
            "processing": true,
            "language":{
                "decimal":",",
                "thousands": ".",
                "sEmptyTable":   "Não foi encontrado nenhum registo",
                "sLoadingRecords": "A carregar...",
                "sProcessing":   "A processar...",
                "sLengthMenu":   "Mostrar _MENU_ registos",
                "sZeroRecords":  "Não foram encontrados resultados",
                "sInfo":         "A mostrar de _START_ até _END_ de _TOTAL_ registos",
                "sInfoEmpty":    "A mostrar de 0 até 0 de 0 registos",
                "sInfoFiltered": "(filtrado de _MAX_ registos no total)",
                "sInfoPostFix":  "",
                "sSearch":       "Pesquisar:",
                "sUrl":          "",
                "oPaginate": {
                    "sFirst":    "Primeiro",
                    "sPrevious": "Anterior",
                    "sNext":     "Seguinte",
                    "sLast":     "Último"
                },
                "oAria": {
                    "sSortAscending":  ": Ordenar colunas de forma ascendente",
                    "sSortDescending": ": Ordenar colunas de forma descendente"
                }
            },
            "columnDefs": [ {
                orderable: false,
                className: 'select-checkbox',
                targets:   0
            } ],
            "select": {
                style:    'multi',
                blurable: false
            },
            "columns": [
                { "data": "id" ,"name":"Peça"},
                { "data": "process_id","name":"Processo" },
                { "data": "description","name":"Descricao"},
                { "data": "status_desc","name":"Estado"},
                { "data": "date_alter","name":"Data" },
                { "data": "pecas","name":"Peças" },
                { "data": "codoem","name":"Oem" },
                { "data": "selling_price_withvat","name":"PVP","type":"num-fmt"},
                { "data": "type_desc","name":"Tipo"},
                { "data": "photo","name":"Foto"},
                { "data": "obs","name":"Observações" },
                { "data": "integrado","name":"Integrado"}
            ],
            "rowCallback": function( row, data ) {
                //
            },
            "ajax": {
                "url": ajax_object.ajax_url,
                "type": "POST",
                "data":function (d) {
                    d.action = data_.action;
                    d.category = data_.category;
                    d.brand = data_.brand;
                    d.part_status = data_.part_status;
                    d.part_types = data_.part_types;
                    d.display_table = data_.display_table;
                    d.apenas_nao_integrados = data_.apenas_nao_integrados;
                }
            }
        });
        pecas_usadas.on( 'select', function ( e, dt, type, indexes ) {
            var rowData = pecas_usadas.rows( indexes ).data()[0]['id'];
            var index = pecas_usadas_selected.indexOf(rowData);
            if (index===-1) {
                pecas_usadas_selected.push(rowData);
            }
        }).on( 'deselect', function ( e, dt, type, indexes ) {
            var index = pecas_usadas_selected.indexOf( pecas_usadas.rows( indexes ).data()[0]['id']);
            if (index!==-1){
                pecas_usadas_selected.splice(index);
            }
        });
        $('#ecoauto_doactionfilter').on('click', function (e) {
            pecas_usadas_selected = [];
            data_.display_table = 1;
            data_.apenas_nao_integrados = $("#apenas_nao_integrados").val();
            data_.category = $("#article_category").val();
            data_.part_status = $("#part_status").val();
            data_.part_types = $("#part_types").val();
            data_.brand = $("#articles_brand").val();
            pecas_usadas.ajax.reload();
        });

        $("#ecoauto_doactionupdate").on('click',function (e) {
            var count = pecas_usadas_selected.length;
            if (count>0){
                var update_data={
                    'action':'updateartigosecoauto',
                    'artigos':pecas_usadas_selected
                };
                $.post(ajax_object.ajax_url,update_data,function (esp) {
                    pecas_usadas_selected=[];
                    pecas_usadas.ajax.reload();
                },'json');
            }
        });

    }
    if ( $("#ecoauto_categories_doactionfilter").length ) {
        var cat_data = {
            'action': 'updatecategoriestable',
            'display_table': 0,
            'apenas_nao_integrados': $("#apenas_nao_integrados_cats").val()
        };
        ecoauto_categories = $('#ecoauto_categorias').DataTable({
            "processing": true,
            "language":{
                "decimal":",",
                "thousands": ".",
                "sEmptyTable":   "Não foi encontrado nenhum registo",
                "sLoadingRecords": "A carregar...",
                "sProcessing":   "A processar...",
                "sLengthMenu":   "Mostrar _MENU_ registos",
                "sZeroRecords":  "Não foram encontrados resultados",
                "sInfo":         "A mostrar de _START_ até _END_ de _TOTAL_ registos",
                "sInfoEmpty":    "A mostrar de 0 até 0 de 0 registos",
                "sInfoFiltered": "(filtrado de _MAX_ registos no total)",
                "sInfoPostFix":  "",
                "sSearch":       "Pesquisar:",
                "sUrl":          "",
                "oPaginate": {
                    "sFirst":    "Primeiro",
                    "sPrevious": "Anterior",
                    "sNext":     "Seguinte",
                    "sLast":     "Último"
                },
                "oAria": {
                    "sSortAscending":  ": Ordenar colunas de forma ascendente",
                    "sSortDescending": ": Ordenar colunas de forma descendente"
                }
            },
            "columnDefs": [ {
                orderable: false,
                className: 'select-checkbox',
                targets:   0
            } ],
            "select": {
                style:    'multi',
                blurable: false
            },
            "columns": [
                { "data": "id" ,"name":"ID"},
                { "data": "description","name":"Descricao"},
                { "data": "integrado","name":"Integrado"}
            ],
            "rowCallback": function( row, data ) {
                //
            },
            "ajax": {
                "url": ajax_object.ajax_url,
                "type": "POST",
                "data":function (d) {
                    d.action = cat_data.action;
                    d.display_table = cat_data.display_table;
                    d.apenas_nao_integrados = cat_data.apenas_nao_integrados;
                }
            }
        });
        ecoauto_categories.on( 'select', function ( e, dt, type, indexes ) {
            var rowData = ecoauto_categories.rows( indexes ).data()[0]['id'];
            var index = ecoauto_categories_selected.indexOf(rowData);
            if (index===-1) {
                ecoauto_categories_selected.push(rowData);
            }
        }).on( 'deselect', function ( e, dt, type, indexes ) {
            var index = ecoauto_categories_selected.indexOf(ecoauto_categories.rows( indexes ).data()[0]['id']);
            if (index!==-1){
                ecoauto_categories_selected.splice(index);
            }
        });
        $('#ecoauto_categories_doactionfilter').on('click', function (e) {
            ecoauto_categories_selected = [];
            cat_data.display_table = 1;
            cat_data.apenas_nao_integrados = $("#apenas_nao_integrados_cats").val();
            ecoauto_categories.ajax.reload();
        });

    }
    if ( $("#ecoauto_componentes_doactionfilter").length ) {
        var comp_data = {
            'action': 'updatecomponentstable',
            'display_table': 0,
            'apenas_nao_integrados': $("#apenas_nao_integrados_comps").val()
        };
        ecoauto_componentes = $('#ecoauto_componentes').DataTable({
            "processing": true,
            "language":{
                "decimal":",",
                "thousands": ".",
                "sEmptyTable":   "Não foi encontrado nenhum registo",
                "sLoadingRecords": "A carregar...",
                "sProcessing":   "A processar...",
                "sLengthMenu":   "Mostrar _MENU_ registos",
                "sZeroRecords":  "Não foram encontrados resultados",
                "sInfo":         "A mostrar de _START_ até _END_ de _TOTAL_ registos",
                "sInfoEmpty":    "A mostrar de 0 até 0 de 0 registos",
                "sInfoFiltered": "(filtrado de _MAX_ registos no total)",
                "sInfoPostFix":  "",
                "sSearch":       "Pesquisar:",
                "sUrl":          "",
                "oPaginate": {
                    "sFirst":    "Primeiro",
                    "sPrevious": "Anterior",
                    "sNext":     "Seguinte",
                    "sLast":     "Último"
                },
                "oAria": {
                    "sSortAscending":  ": Ordenar colunas de forma ascendente",
                    "sSortDescending": ": Ordenar colunas de forma descendente"
                }
            },
            "columnDefs": [ {
                orderable: false,
                className: 'select-checkbox',
                targets:   0
            } ],
            "select": {
                style:    'multi',
                blurable: false
            },
            "columns": [
                { "data": "id" ,"name":"ID"},
                { "data": "description","name":"Descricao"},
                { "data": "integrado","name":"Integrado"}
            ],
            "rowCallback": function( row, data ) {
                //
            },
            "ajax": {
                "url": ajax_object.ajax_url,
                "type": "POST",
                "data":function (d) {
                    d.action = comp_data.action;
                    d.display_table = comp_data.display_table;
                    d.apenas_nao_integrados = comp_data.apenas_nao_integrados;
                }
            }
        });
        ecoauto_componentes.on( 'select', function ( e, dt, type, indexes ) {
            var rowData = ecoauto_componentes.rows( indexes ).data()[0]['id'];
            var index = ecoauto_componentes_selected.indexOf(rowData);
            if (index===-1) {
                ecoauto_componentes_selected.push(rowData);
            }
        }).on( 'deselect', function ( e, dt, type, indexes ) {
            var index = ecoauto_componentes_selected.indexOf(ecoauto_componentes.rows( indexes ).data()[0]['id']);
            if (index!==-1){
                ecoauto_componentes_selected.splice(index);
            }
        });
        $('#ecoauto_componentes_doactionfilter').on('click', function (e) {
            ecoauto_componentes_selected = [];
            comp_data.display_table = 1;
            comp_data.apenas_nao_integrados = $("#apenas_nao_integrados_cats").val();
            ecoauto_componentes.ajax.reload();
        });

    }
    if ( $("#officegest_doactionfilter").length ) {
        var data = {
            'action': 'updatearticlestable',
            'family': $("#articles_family").val(),
            'brand': $("#articles_brand").val(),
            'apenas_nao_integrados':$("#apenas_nao_integrados").val(),
            'display_table': 0
        };
        artigos = $('#articles').DataTable({
               "processing": true,
               "language":{
                   "decimal":",",
                   "thousands": ".",
                   "sEmptyTable":   "Não foi encontrado nenhum registo",
                   "sLoadingRecords": "A carregar...",
                   "sProcessing":   "A processar...",
                   "sLengthMenu":   "Mostrar _MENU_ registos",
                   "sZeroRecords":  "Não foram encontrados resultados",
                   "sInfo":         "A mostrar de _START_ até _END_ de _TOTAL_ registos",
                   "sInfoEmpty":    "A mostrar de 0 até 0 de 0 registos",
                   "sInfoFiltered": "(filtrado de _MAX_ registos no total)",
                   "sInfoPostFix":  "",
                   "sSearch":       "Pesquisar:",
                   "sUrl":          "",
                   "oPaginate": {
                       "sFirst":    "Primeiro",
                       "sPrevious": "Anterior",
                       "sNext":     "Seguinte",
                       "sLast":     "Último"
                   },
                   "oAria": {
                       "sSortAscending":  ": Ordenar colunas de forma ascendente",
                       "sSortDescending": ": Ordenar colunas de forma descendente"
                   }
               },
               "columnDefs": [ {
                    orderable: false,
                    className: 'select-checkbox',
                    targets:   0
                } ],
               "select": {
                    style:    'multi',
                    blurable: false
                },
               "columns": [
                    { "data": "id" ,"name":"Artigo"},
                    { "data": "description","name":"Descrição" },
                    { "data": "marca","name":"Marca"},
                    { "data": "barcode","name":"EAN" },
                    { "data": "familia","name":"Familia" },
                    { "data": "pvp","name":"PVP","type":"num-fmt"},
                    { "data": "pvp_iva","name":"PVP / Iva","type":"num-fmt"},
                    { "data": "tipo_artigo","name":"Tipo de Artigo"},
                    { "data": "integrado","name":"Integrado"}
                ],
               "rowCallback": function( row, data ) {
                   //
                },
               "ajax": {
                    "url": ajax_object.ajax_url,
                    "type": "POST",
                    "data":function (d) {
                        d.action = data.action;
                        d.family = data.family;
                        d.brand = data.brand;
                        d.display_table = data.display_table;
                        d.apenas_nao_integrados = data.apenas_nao_integrados;
                    }
                }
            });
        artigos.on( 'select', function ( e, dt, type, indexes ) {
            var rowData = artigos.rows( indexes ).data()[0]['id'];
            var index = artigos_selected.indexOf(rowData);
            if (index===-1) {
                artigos_selected.push(rowData);
            }
        }).on( 'deselect', function ( e, dt, type, indexes ) {
            var index = artigos_selected.indexOf( artigos.rows( indexes ).data()[0]['id']);
            if (index!==-1){
                artigos_selected.splice(index);
            }
        });

        $('#officegest_doactionfilter').on('click', function (e) {
           artigos_selected = [];
           data.display_table = 1;
           data.apenas_nao_integrados = $("#apenas_nao_integrados").val();
           data.family = $("#articles_family").val();
           data.brand = $("#articles_brand").val();
           artigos.ajax.reload();
        });
        $("#tipo_classificacao").hide();
        $("#tipo_actualizacao").select2().on("select2:select", function (e) {
            var selected_element = $(e.currentTarget);
            var select_val = selected_element.val();
            if (select_val == 'CLASSIFICACAO'){
                $("#tipo_classificacao").show();
            }
            else{
                $("#tipo_classificacao").hide();
                $("#tipo_classificacao").val('-1');
            }
        }).trigger("select2:select");
        $("#select_all").on('click',function (e) {
            artigos_selected = [];
            artigos.select().rows();
            artigos.rows().select();
        });

        $("#doactionupdate").on('click',function (e) {
            var count = artigos_selected.length;
            console.log(count);
            if (count>0){
                var update_data={
                    'action':'updateartigos',
                    'tipo':$("#tipo_actualizacao").val(),
                    'classificacao':$("#tipo_classificacao").val(),
                    'artigos':artigos_selected
                };
                $.post(ajax_object.ajax_url,update_data,function (esp) {
                    artigos_selected=[];
                    artigos.ajax.reload();
                },'json');
            }
        });
    }

});