urlEliminar='/empresa/eliminar';

$(document).ready(function(){
	cargaGridfrmEmpresa();
});

function cargaGridfrmEmpresa() {
	
	var filtro="/empresa/grid";
	if($("#fnombre").val() != "") filtro+="/nombre/"+$("#fnombre").val();
	if($("#frazon").val()  != "") filtro+="/razon/"+$("#frazon").val();
	if($("#fstatus").val() != "") filtro+="/status/"+$("#fstatus").val();
		
	$("#flexigrid").flexigrid({
		url: filtro,
		dataType: "xml",
		colModel: [
			{display: "Nombre",                                             name: "nombre",                               width: 150, sortable: true, align: "center"},
			{display: "Raz&oacute;n social",                                name: "razon_social",                         width: 280, sortable: true, align: "center"},
			{display: "Nombre Contpaq",                                     name: "nombre_bd_contpaq",                    width: 150, sortable: true, align: "center"},
			{display: "Usuario Contpaq",                                    name: "usuario_bd_contpaq",                   width: 150, sortable: true, align: "center"},
			{display: "Contraseña Contpaq",                                 name: "pass_bd_contpaq",                      width: 150, sortable: true, align: "center"},
			{display: "Servidor Contpaq",                                   name: "server_bd_contpaq",                    width: 200, sortable: true, align: "center"},
			{display: "Tasa",                                               name: "tasa",                                 width: 200, sortable: true, align: "center"},
			{display: "#Cuenta ISR Retenido",                               name: "isr_retenido",                         width: 150, sortable: true, align: "center"},
			{display: "Retención salarios",                                 name: "retencion_salarios",                   width: 150, sortable: true, align: "center"},
			{display: "Retención ISR honorarios",                           name: "retencion_isr_honorarios",             width: 150, sortable: true, align: "center"},
			{display: "Retención asimilados",                               name: "retencion_asimilados",                 width: 150, sortable: true, align: "center"},
			{display: "Retención dividendos",                               name: "retencion_dividendos",                 width: 150, sortable: true, align: "center"},
			{display: "Retención intereses",                                name: "retencion_intereses",                  width: 150, sortable: true, align: "center"},
			{display: "Retención pagos al extranjero",                      name: "retencion_pagos_extranjero",           width: 150, sortable: true, align: "center"},
			{display: "Retención venta de acciones",                        name: "retencion_venta_acciones",             width: 150, sortable: true, align: "center"},
			{display: "Retención venta de partes sociales",                 name: "retencion_venta_partes_sociales",      width: 150, sortable: true, align: "center"},
			{display: "Retención ISR arrendamiento",                        name: "retencion_isr_arrendamiento",          width: 150, sortable: true, align: "center"},
			{display: "Perdida de Ejercicios Anteriores Enero y febrero 1", name: "ejercicio_anterior_1_enero",           width: 150, sortable: true, align: "center"},
			{display: "Perdida de Ejercicios Anteriores Enero y febrero 2", name: "ejercicio_anterior_1_febrero",         width: 150, sortable: true, align: "center"},
			{display: "Perdida de Ejercicios Anteriores Marzo - Diciembre", name: "ejercicio_anterior_marzo_diciembre_1", width: 150, sortable: true, align: "center"},
			// {display: "Perdida de Ejercicios Anteriores Febrero 2", name: "ejercicio_anterior_2_febrero",    width: 150, sortable: true, align: "center"},
			{display: "Tipo de empresa",                                    name: "tipo_empresa_id",                      width: 150, sortable: true, align: "center"},
			{display: "Estatus",                                            name: "status",                               width: 80,  sortable: true, align: "center"},
		],
		sortname: "nombre"
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
				var idEmpresa = $(this).find(".registro").attr("rel").valueOf();
				$("#id").val(idEmpresa);
				
				obtenerEmpresa(idEmpresa);
			});
		}
	});
}

/**
 * obtenerEmpresa
 **/
function obtenerEmpresa(id) {
	
	$.ajax({
		type: "POST"
		,url: "/empresa/obtener"
		,dataType: "json"
		,processData:true
		,data: { id : id }
		,success: function(re){
			
			if(re != '' && re != undefined && re.error == '') {
				
				$("#nombre").val(re.nombre);
				$("#razon_social").val(re.razon_social);
				$("#nombre_bd_contpaq").val(re.nombre_bd_contpaq);
				$("#usuario_bd_contpaq").val(re.usuario_bd_contpaq);
				$("#pass_bd_contpaq").val(re.pass_bd_contpaq);
				$("#server_bd_contpaq").val(re.server_bd_contpaq);
				$("#coeficiente_utilidad").val(re.coeficiente_utilidad);
				$("#tasa").val(re.tasa);
				$("#isr_retenido").val(re.isr_retenido);
				$("#retencion_salarios").val(re.retencion_salarios);
				$("#retencion_isr_honorarios").val(re.retencion_isr_honorarios);
				$("#retencion_asimilados").val(re.retencion_asimilados);
				$("#retencion_dividendos").val(re.retencion_dividendos);
				$("#retencion_intereses").val(re.retencion_intereses);
				$("#retencion_pagos_extranjero").val(re.retencion_pagos_extranjero);
				$("#retencion_venta_acciones").val(re.retencion_venta_acciones);
				$("#retencion_venta_partes_sociales").val(re.retencion_venta_partes_sociales);
				$("#retencion_isr_arrendamiento").val(re.retencion_isr_arrendamiento);
				$("#ejercicio_anterior_enero_febrero_1").val(re.ejercicio_anterior_enero_febrero_1);
				$("#ejercicio_anterior_enero_febrero_2").val(re.ejercicio_anterior_enero_febrero_2);
				$("#ejercicio_anterior_marzo_diciembre_1").val(re.ejercicio_anterior_marzo_diciembre_1);
				// $("#ejercicio_anterior_2_febrero").val(re.ejercicio_anterior_2_febrero);
				$("#tipo_empresa_id").val(re.tipo_empresa_id);
				$("#status").val(re.status).change();
				$("#eliminar").attr("onclick", "eliminar('"+re.id+"', '"+re.status+"', 'frmEmpresa')");
			}
			else {
				_mensaje("#_mensaje-1", re.error);
			}
		}//success
		,error: function(error){
			_mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado, int&eacute;ntelo de nuevo");
		} //error
	}) 
}

/**
 * limpiarfrmEmpresa
 **/
function limpiarfrmEmpresa() {
	$("#frmEmpresa")[0].reset();
	$("#eliminar").attr("onclick","eliminar('', '1')");
	$("#id").val('');
}