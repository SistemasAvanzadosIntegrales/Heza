/**
 * @package      autorizacion.js
 * @author:      Danny Ramirez
 * @contact:     danny_ramirez@avansys.com.mx
 * @description: 
 * @version:     1.0
 * @path call:   autorizacion/index.phtml
 * @copyright:   Avansys
 **/

$(document).ready(function() {
	
	var filtro = "/autorizacion/grid";
	// if( $("#fstatus").val() != "" ) filtro += "/status/"+$("#fstatus").val();
	
	// console.log(filtro);
	
	$("#flexigrid").flexigrid({
		url: filtro,
		dataType: "xml",
		colModel: [
			{display: "Empresa",     name: "empresa",     width: 150, sortable: true,  align: "center"},
			{display: "Ejercicio",   name: "ejercicio",   width: 100, sortable: true,  align: "center"},
			{display: "Periodo",     name: "periodo",     width: 100, sortable: true,  align: "center"},
			{display: 'Usuario',     name: "usuario",     width: 150, sortable: true,  align: 'center'},
			{display: 'Fecha',       name: "fecha",       width: 150, sortable: true,  align: 'center'},
			{display: 'Estatus',     name: "status",      width: 100, sortable: true,  align: 'center'},
			{display: 'Resolución',  name: "resolucion",  width: 100, sortable: true,  align: 'center'},
			{display: 'Resolvio',    name: "resolvio",    width: 100, sortable: true,  align: 'center'},
			{display: 'Comentarios', name: "comentarios", width: 150, sortable: false, align: 'center'},
			{display: 'Autorizar',   name: "autorizar",   width: 100, sortable: false, align: 'center'}
		],
		sortname: "status_resuelto",
		sortorder: "asc",
		usepager: true,
		useRp: false,
		singleSelect: true,
		resizable: false,
		showToggleBtn: false,
		rp: 10,
		width: 1200,
		height: 400
	});
});

/**
 * autorizar()
 **/
function autorizar(id, nombre) {
	
	$.ajax({
		type: "POST",
		url: "/autorizacion/ver-autorizacion",
		data: {
			id     : id,
			nombre : nombre
		},
		success: function(html){
			
			$("#_dialogo-2").html(html);
			
			$("#_dialogo-2").dialog({
				width: "900"
				,title: "Autorización"
				,resizable: false
				,draggable: false
				,position: "center"
				,modal: true
				,buttons: [
					{
						id: "aceptar-1"
						,text: "Aceptar"
						,class: "btn btn-rojo-1"
						,click: function(){
							guardarAutorizacion(id, nombre);
						}
					}
					,{
						id: "cancelar-1"
						,text: "Cerrar"
						,class: "btn btn-rojo-1"
						,click: function(){
							$("#_dialogo-2").dialog("destroy");
						}
					}
				]
			})
		},
		error: function(respuesta) {
			_mensaje("#_mensaje-1", "Ocurri&oacute; un error al tratar de abrir la ventana, int&eacute;ntelo de nuevo");
		}
	})
}

/**
 * guardarAutorizacion
 **/
function guardarAutorizacion(id, nombre) {
	
	$( "#frm-check" ).validate({
		submitHandler: function(form) {
			$(form).ajaxSubmit({
				success: function(respuesta){
					location.reload();
				} //success
				,error: function(respuesta){
					_mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado, int&eacute;ntelo de nuevo");
				} //error
			}) //ajaxSubmit
		}
	});
	
	$( "#frm-check" ).submit();
}
