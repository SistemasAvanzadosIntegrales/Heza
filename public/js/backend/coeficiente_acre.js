urlEliminar='/coeficiente-acre/eliminar';

/**
 * @function     Document Ready
 * @author:      Danny Ramirez
 * @contact:     roberto_ramirez@avansys.com.mx
 * @description: Funcion para cargar empresas en selector.
 * @version:     1.0
 * @path call:   coeficiente/index.phtml
 * @copyright:   Avansys
 **/
$(document).ready(function(){
	
	cargaGridfrmCoeficiente();
	
	//funciones para chosen
	$( '#fempresa_id' ).chosen({ width:"250px" });
	$( '#fejercicio_id' ).chosen({ width:"250px" });
	
	cargaEmpresas();
	cargaEjercicios();
	
});

/**
 * @function     cargaEmpresas
 * @author:      Danny Ramirez
 * @contact:     roberto_ramirez@avansys.com.mx
 * @description: Funcion para cargar empresas en selector.
 * @version:     1.0
 * @path call:   coeficiente/index.phtml
 * @copyright:   Avansys
 **/
function cargaEmpresas (){
	
	$.ajax({
		type: "POST",
		url: "/pago-provisionalpm/obtenerempresas",
		dataType: "json",
		success:function ( data ){
			
			$.each(data, function (i, item) {
				
				$( '#fempresa_id' ).append($('<option>', {
					value: item.id,
					text : item.nombre
				}));
				
				$( '#id_empresa' ).append($('<option>', {
					value: item.id,
					text : item.nombre
				}));
				
				$( '#fempresa_id' ).val('');
				$( '#fempresa_id' ).trigger("chosen:updated");
			});
		},
		error: function (error){
			_mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado al cargar empresas");
		}
	});
}

/**
 * @function     cargaEjercicios
 * @author:      Danny Ramirez
 * @contact:     roberto_ramirez@avansys.com.mx
 * @description: Funcion para cargar empresas en selector.
 * @version:     1.0
 * @path call:   coeficiente/index.phtml
 * @copyright:   Avansys
 **/
function cargaEjercicios (){
	
	$.ajax({
		type: "POST",
		url: "/pago-provisionalpm/obtenerejercicios",
		dataType: "json",
		success:function( data ){
			
			$.each(data, function (i, item) {
				$( '#fejercicio_id' ).append($('<option>', {
					value: item.id,
					text : item.nombre
				}));
				
				$( '#id_ejercicio' ).append($('<option>', {
					value: item.id,
					text : item.nombre
				}));
				
				$( '#fejercicio_id' ).val('');
				$( '#fejercicio_id' ).trigger("chosen:updated");
			});
		},
		error: function(error){
			_mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado al cargar ejercicios");
		}
	});
}

/**
 * @function     cargaGridfrmCoeficiente
 * @author:      Danny Ramirez
 * @contact:     roberto_ramirez@avansys.com.mx
 * @description: Funcion para cargar coeficientes en tabla.
 * @version:     1.0
 * @path call:   coeficiente/index.phtml
 * @copyright:   Avansys
 **/
function cargaGridfrmCoeficiente() {
	
	var filtro="/coeficiente-acre/grid";
	// if($("#fnombre").val() != "") filtro+="/nombre/"+$("#fnombre").val();
	// if($("#frazon").val()  != "") filtro+="/razon/"+$("#frazon").val();
	// if($("#fstatus").val() != "") filtro+="/status/"+$("#fstatus").val();
		
	$("#flexigrid").flexigrid({
		url: filtro,
		dataType: "xml",
		colModel: [
			{display: "Empresa",                       name: "id_empresa",                 width: 182, sortable: true, align: "center"},
			{display: "Ejercicio",                     name: "id_ejercicio",               width: 100, sortable: true, align: "center"},
			{display: "Periodo Inicio",                name: "id_periodo_inicio",          width: 100, sortable: true, align: "center"},
			{display: "Periodo Fin",                   name: "id_periodo_fin",             width: 100, sortable: true, align: "center"},
			{display: "Coeficiente de acreditamiento", name: "coeficiente_acreditamiento", width: 180, sortable: true, align: "center"},
		],
		sortname: "id_empresa"
		,sortorder: "asc"
		,usepager: true
		,useRp: false
		,singleSelect: true
		,resizable: false
		,showToggleBtn: false
		,rp: 10
		,width: "auto"
		,height: 400
		,onSuccess: function(){
			$("#flexigrid tr").bind('click',function(){
				
				//obtenemos el id de la empresa
				var idCoeficiente = $(this).find(".registro").attr("rel").valueOf();
				$("#id").val(idCoeficiente);
				
				obtenerCoeficiente(idCoeficiente);
			});
		}
	});
}

/**
 * @function     obtenerCoeficiente
 * @author:      Danny Ramirez
 * @contact:     roberto_ramirez@avansys.com.mx
 * @description: Funcion para cargar coeficientes en tabla.
 * @version:     1.0
 * @path call:   coeficiente/index.phtml
 * @copyright:   Avansys
 **/
function obtenerCoeficiente(id) {
	
	$.ajax({
		type: "POST"
		,url: "/coeficiente-acre/obtener"
		,dataType: "json"
		,processData:true
		,data: { id : id }
		,success: function(re){
			
			if(re != '' && re != undefined && re.error == '') {
				
				$("#id").val(re.id);
				$("#id_empresa").val(re.id_empresa);
				$("#id_ejercicio").val(re.id_ejercicio);
				$("#id_periodo_inicio").val(re.id_periodo_inicio);
				$("#id_periodo_fin").val(re.id_periodo_fin);
				$("#coeficiente_acreditamiento").val(re.coeficiente_acreditamiento);
				$("#eliminar").attr("onclick", "eliminar('"+re.id+"', '1', 'frmCoeficiente')");
			}
			else {
				_mensaje("#_mensaje-1", re.error);
			}
		}//success
		,error: function(error){
			_mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado, int&eacute;ntelo de nuevo");
		} //error
	});
}

/**
 * @function     limpiarfrmCoeficiente
 * @author:      Danny Ramirez
 * @contact:     roberto_ramirez@avansys.com.mx
 * @description: Funcion para cargar coeficientes en tabla.
 * @version:     1.0
 * @path call:   coeficiente/index.phtml
 * @copyright:   Avansys
 **/
function limpiarfrmCoeficiente() {
	$("#frmCoeficiente")[0].reset();
	$("#eliminar").attr("onclick","eliminar('', '1')");
	$("#id").val('');
}
