/**
 * @package      compensancion.js
 * @author:      
 * @contact:     
 * @description: 
 * @version:     1.0
 * @path call:   subsidio-empleo/index.phtml
 * @copyright:   Avansys
 **/
 
urlEliminar = '/compensacion/eliminar';

/**
 * Document Ready
 **/
$(document).ready(function() {
	
	$('.filtros a').toggleClass('disabled',true);
	$('.acciones button').toggleClass('disabled',true);
	
	//Funciones para la carga de empresas
	$('#fempresa_id').chosen({ width:"250px" });
	cargaEmpresas();
	$("#monto_aplicar").blur(function() {
		var rem_des = $('#remanente_antes').val();
		rem_des     = rem_des - $(this).val();
		rem_des     = Number(rem_des);
		$('#fremanente_despues').val(rem_des.toFixed(2));
	});
	
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
			$('.filtros a').toggleClass('disabled',false);
			$('.acciones button').toggleClass('disabled',false);
			$('#id_ejercicio').val(params.selected);
			cargaGridfrmCompensacion();
		}
	});
});

/**
 * @function     cargaGridfrmCompensacion
 * @author:      
 * @contact:     
 * @description: 
 * @version:     1.0
 * @path call:   subsidio-empleo/index.phtml
 * @copyright:   Avansys
 **/
function cargaGridfrmCompensacion () {
	
	var empresa   = $("#id_empresa").val();
	var ejercicio = $("#id_ejercicio").val();
	
	$.ajax({
		type: "POST",
		url: "/compensacion/obtener-remanente/empresa_id/"+empresa+"/ejercicio_id/"+ejercicio,
		success:function(data){
			var rem = Number(data); 
			rem     = rem.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
			$("#remanenteTotal").text('$'+rem);
			$('#remanente_antes').val(data);
		},
		error: function(error){
			_mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado al cargar ejercicios");
		}
	});
	
	$("#flexigrid").flexigrid({
		url: "/compensacion/grid/empresa_id/"+empresa+"/ejercicio_id/"+ejercicio,
		dataType: "xml",
		colModel: [
			{display: "Fecha",          name: "fecha",            width: 100, sortable: true, align: "center"},
			{display: "Tipo de Imp",    name: "tipo_impuesto",    width: 100, sortable: true, align: "center"},
			{display: "Periodo",        name: "periodo",          width: 70,  sortable: true, align: "center"},
			{display: "Ejercicio",      name: "ejercicio",        width: 70,  sortable: true, align: "center"},
			{display: "Declaración",    name: "tipo_declaracion", width: 100, sortable: true, align: "center"},
			{display: "Num Operación",  name: "numero_operacion", width: 100, sortable: true, align: "center"},
			{display: "Monto Original", name: "monto_original",   width: 100, sortable: true, align: "center"},
			{display: "Monto Aplicado", name: "monto_aplicado",   width: 100, sortable: true, align: "center"},
		],
		sortname: "fecha",
		sortorder: "asc",
		usepager: true,
		useRp: false,
		resizable: false,
		rp: 24,
		width: "auto",
		height: 328,
		onDoubleClick: function(data){
			//obtenemos el id de la compensacion
			var idCompensacion = $(data).find(".registro").attr("rel").valueOf();
			$("#id").val(idCompensacion);
			obtenerCompensacion(idCompensacion);
		}
	});
	
	$("#flexigrid-1").flexigrid({
		url: "/compensacion/grid-aplicacion/empresa_id/"+empresa+"/ejercicio_id/"+ejercicio,
		dataType: "xml",
		colModel: [
			{display: "Periodo",        name: "periodo_id",  width: 300, sortable: true, align: "center"},
			{display: "Impuesto",       name: "impuesto_id", width: 300, sortable: true, align: "center"},
			{display: "Monto aplicado", name: "monto",       width: 300, sortable: true, align: "center"},
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
	
	urlAction  = "/compensacion/grid/empresa_id/"+empresa+"/ejercicio_id/"+ejercicio;
	urlAction1 = "/compensacion/grid-aplicacion/empresa_id/"+empresa+"/ejercicio_id/"+ejercicio;
	
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
 * @function     obtenerCompensacion
 * @author:      
 * @contact:     
 * @description: 
 * @version:     1.0
 * @path call:   subsidio-empleo/index.phtml
 * @copyright:   Avansys
 **/
function obtenerCompensacion (id) {
	
	$.ajax({
		type: "POST",
		url: "/compensacion/obtener",
		dataType: "json",
		processData:true,
		data: {id:id },
		success: function(re){
			if( re != '' && re != undefined && re.error == '' ) {
				
				$("#tipo_impuesto").val(re.tipo_impuesto);
				$("#periodo").val(re.periodo);
				$("#tipo_declaracion").val(re.tipo_declaracion);
				$("#numero_operacion").val(re.numero_operacion);
				$("#monto_original").val(re.monto_original);
				$("#monto_aplicar").val(re.monto_aplicar);
				
				$("#eliminar").attr("onclick","eliminar('"+re.id+"','"+re.status+"','frmCompensacion');limpiarfrmCompensacion();");
			}
		}//success
		,error: function(error){
			_mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado, int&eacute;ntelo de nuevo");
		} //error
	})
}

/**
 * @function     obtenerAplsCompen
 * @author:      
 * @contact:     
 * @description: 
 * @version:     1.0
 * @path call:   subsidio-empleo/index.phtml
 * @copyright:   Avansys
 **/
function obtenerAplsCompen(id_compensacion) {
	
	$("#flexigrid-1").flexOptions({
		url: '/compensacion/grid-aplicacion/id_compensacion/'+id_compensacion,
		dataType: "xml",
		onSuccess: function(){
			
		}
	}).flexReload();
}

/**
 * @function     obtenerAplsCompen
 * @author:      
 * @contact:     
 * @description: 
 * @version:     1.0
 * @path call:   subsidio-empleo/index.phtml
 * @copyright:   Avansys
 **/
function limpiarfrmCompensacion() {
	
	$("#frmCompensacion")[0].reset();
	$("#eliminar").attr("onclick", "eliminar('', '1')");
	$("#id").val('');
	cargaGridfrmCompensacion ();
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
	
	var file = "/compensacion/imprimir/empresa_id/"+empresa+"/ejercicio_id/"+ejercicio+"";
	
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
	
	var file = "/compensacion/exportar/empresa_id/"+empresa+"/ejercicio_id/"+ejercicio+"";
	
	window.open(file);
}