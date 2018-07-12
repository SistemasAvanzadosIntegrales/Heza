/**
 * @package      subsidio-empleo.js
 * @author:      
 * @contact:     
 * @description: 
 * @version:     1.0
 * @path call:   subsidio-empleo/index.phtml
 * @copyright:   Avansys
 **/

/**
 * Document Ready
 **/
$(document).ready(function() {
	
	$('.filtros a').toggleClass('disabled', true);
	
	//Funciones para la carga de empresas
	$('#fempresa_id').chosen({ width:"250px" });
	
	cargaEmpresas();
	
	//Funciones para la carga de ejercicios
	$('#fejercicio_id').chosen({ width:"250px" });
	
	//Funcion para cargar ejercicios al seleccionar la empresa
	$('#fempresa_id').on('change', function(evt, params) {
		
		$('#id_empresa').val(params.selected);
		$('.filtros a').toggleClass('disabled', true);
		
		$.ajax({
			type: "POST",
			url: "/pago-provisionalpm/obtenerejercicios",
			dataType: "json",
			data: {id_empresa : params.selected},
			success:function(data){
				
				$("#fejercicio_id option").remove();
				
				$.each(data, function (i, item) {
					$('#fejercicio_id').append($('<option>', {
						value: item.id,
						text : item.nombre
					}));
					
					$('#fejercicio_id').val('');
					$('#fejercicio_id').trigger("chosen:updated");
				});
			},
			error: function(error){
				_mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado al cargar ejercicios");
			}
		});
	});
	
	//Evento despues de seleccionar ejercicio
	$('#fejercicio_id').on('change', function(evt, params) {
		if( params.selected != 0 ) {
			$('.filtros a').toggleClass('disabled', false);
			$('#id_ejercicio').val(params.selected);
			
			cargaGridfrmSubsidioEmpleo();
		}
	});
	
	$('#fmonto').blur(function() {
		$('#fremanente').val($(this).val());
		$('#remanente_hi').val($(this).val());
	});
});

/**
 * @function     cargaGridfrmSubsidioEmpleo
 * @author:      
 * @contact:     
 * @description: 
 * @version:     1.0
 * @path call:   subsidio-empleo/index.phtml
 * @copyright:   Avansys
 **/
function cargaGridfrmSubsidioEmpleo () {
	
	var empresa   = $("#id_empresa").val();
	var ejercicio = $("#id_ejercicio").val();
	
	$.ajax({
		type: "POST",
		url: "/subsidio-empleo/obtener-remanente/empresa_id/"+empresa+"/ejercicio_id/"+ejercicio,
		success:function(data){
			$( "#remanente" ).text(data);
		},
		error: function(error){
			_mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado al cargar ejercicios");
		}
	});
	
	$("#flexigrid").flexigrid({
		url: "/subsidio-empleo/grid/empresa_id/"+empresa+"/ejercicio_id/"+ejercicio,
		dataType: "xml",
		colModel: [
			{display: "Fecha",     name: "periodo",   width: 300, sortable: true, align: "center"},
			{display: "Monto",     name: "monto",     width: 300, sortable: true, align: "center"},
			{display: "Remanente", name: "remanente", width: 300, sortable: true, align: "center"},
		],
		usepager: false,
		useRp: false,
		singleSelect: true,
		resizable: false,
		showToggleBtn: false,
		rp: 12,
		width: "auto",
		height: 260
	});
	
	$("#flexigrid-1").flexigrid({
		url: "/subsidio-empleo/grid-aplicacion/empresa_id/"+empresa+"/ejercicio_id/"+ejercicio,
		dataType: "xml",
		colModel: [
			{display: "Periodo",        name: "periodo_id",  width: 300, sortable: true, align: "center"},
			{display: "Impuesto",       name: "impuesto_id", width: 300, sortable: true, align: "center"},
			{display: "Monto aplicado", name: "monto",    width: 300, sortable: true, align: "center"},
		],
		sortname: "impuesto_id",
		sortorder: "asc",
		usepager: false,
		useRp: false,
		singleSelect: true,
		resizable: false,
		showToggleBtn: false,
		rp: 12,
		width: "auto",
		height: 260
	});
	
	urlAction  = "/subsidio-empleo/grid/empresa_id/"+empresa+"/ejercicio_id/"+ejercicio;
	urlAction1 = "/subsidio-empleo/grid-aplicacion/empresa_id/"+empresa+"/ejercicio_id/"+ejercicio;
	
	$("#flexigrid").flexOptions({
		url: urlAction,
		newp: 1
	}).flexReload();
	
	$("#flexigrid-1").flexOptions({
		url: urlAction1,
		newp: 1
	}).flexReload();
}

/**
 * @function     cargaEmpresas
 * @author:      
 * @contact:     
 * @description: Funcion para cargar empresas en selector.
 * @version:     1.0
 * @path call:   subsidio-empleo/index.phtml
 * @copyright:   Avansys
 **/
function cargaEmpresas () {
	
	$.ajax({
		type: "POST",
		url: "/pago-provisionalpm/obtenerempresas",
		dataType: "json",
		success:function(data){
			
			$.each(data, function (i, item) {
				$('#fempresa_id').append($('<option>', { 
					value: item.id,
					text : item.nombre
				}));
				$('#fempresa_id').val('');
				$('#fempresa_id').trigger("chosen:updated");
			});
		},
		error: function(error){
			_mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado al cargar empresas");
		}
	});
}

/**
 * @function     imprimir
 * @author:      
 * @contact:     
 * @description: Funcion para cargar empresas en selector.
 * @version:     1.0
 * @path call:   subsidio-empleo/index.phtml
 * @copyright:   Avansys
 **/
function imprimir(){
	
	var empresa   = $("#id_empresa").val();
	var ejercicio = $("#id_ejercicio").val();
	
	var file = "/subsidio-empleo/imprimir/empresa_id/"+empresa+"/ejercicio_id/"+ejercicio+"";
	
	window.open(file);
}

/**
 * @function     exportar
 * @author:      
 * @contact:     
 * @description: Funcion para cargar empresas en selector.
 * @version:     1.0
 * @path call:   subsidio-empleo/index.phtml
 * @copyright:   Avansys
 **/
function exportar(){
	
	var empresa   = $("#id_empresa").val();
	var ejercicio = $("#id_ejercicio").val();
	
	var file = "/subsidio-empleo/exportar/empresa_id/"+empresa+"/ejercicio_id/"+ejercicio+"";
	
	window.open(file);
}
