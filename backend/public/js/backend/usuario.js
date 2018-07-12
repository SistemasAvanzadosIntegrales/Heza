/**
 * @package      usuario.js
 * @author:      
 * @contact:     
 * @description: 
 * @version:     1.0
 * @path call:   usuario/index.phtml
 * @copyright:   Avansys
 **/

urlEliminar='/usuario/eliminar';

$(document).ready(function() {
	
	var filtro = "/usuario/grid";
	if( $("#fnombre").val() != "" ) filtro += "/nombre/"+$("#fnombre").val();
	if( $("#fstatus").val() != "" ) filtro += "/status/"+$("#fstatus").val();
	
	$("#flexigrid").flexigrid({
		url: filtro,
		dataType: "xml",
		colModel: [
			{display: "Nombre",                    name: "nombre",             width: 450, sortable: true, align: "center"},
			{display: "Correo electr&oacute;nico", name: "correo_electronico", width: 450, sortable: true, align: "center"},
			{display: 'Permisos',                  name: "permisos",           width: 100, sortable: false, align: 'center'},
			{display: 'Editar',                    name: "editar",             width: 100, sortable: false, align: 'center'},
			{display: 'Eliminar',                  name: "eliminar",           width: 100, sortable: false, align: 'center'}
		],
		sortname: "nombre",
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
 * @function     permisos
 * @author:      
 * @contact:     
 * @description: 
 * @version:     1.0
 * @path call:   usuario/index.phtml
 * @copyright:   Avansys
 **/
function permisos (id) {
	
	$.ajax({
		type: "POST",
		url: "/usuario/permisos",
		data: { id: id },
		success: function(html){
			
			$("#_dialogo-1").html(html);
			
			$("#frm1").validate({
				submitHandler: function(form){
					$("#es-1").removeClass('hide');
					$("#frm1").addClass('hide');
					$("#aceptar-1").addClass('hide');
					$("#cancelar-1").addClass('hide');
					$(form).ajaxSubmit({
						success: function(respuesta){
							$("#_dialogo-1").dialog("close");
						}
					})
				}
			})
			
			$("#_dialogo-1").dialog({
				width: "900",
				height: "auto",
				title: "Usuario",
				resizable: false,
				draggable: false,
				modal: true,
				buttons: [
				]//buttons
			})//dialog
		}
	});
}//function

/**
 * @function     guardarPermisos
 * @author:      
 * @contact:     
 * @description: 
 * @version:     1.0
 * @path call:   usuario/index.phtml
 * @copyright:   Avansys
 **/
function guardarPermisos() {
	
	$( "#disponibles input" ).each(function( index ) {
		$( this ).prop( "checked", false );
	});
	
	$( "#seleccionados input" ).each(function( index ) {
		$( this ).prop( "checked", true );
	});
	
	$("#frm-1").validate({
		submitHandler: function(form){
			$(form).ajaxSubmit({
				success: function(respuesta){
					
					if(!isNaN(respuesta)){
						$("#_dialogo-1").dialog("close");
						window.location = "/usuario";
					}
					else {
						_mensaje("#_mensaje-1",  respuesta );
					}
					
				} //success
				,error: function(respuesta){
					_mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado, int&eacute;ntelo de nuevo");
				} //error
			}) //ajaxSubmit
		} //submitHandler
	}) //validate
	
	$("#frm-1").submit();
}

/**
 * Selector Multiple Doble
 **/

/**
 * @function     filtrarEmpresas
 * @author:      Danny Ramirez
 * @contact:     danny_ramirez@avansys.com.mx
 * @description: Buscar empresas
 * @version:     1.0
 * @path call:   usuario/index.phtml
 * @copyright:   Avansys
 **/
function filtrarEmpresas (id) {
	
	//Disabled Node multiselect
	$( '#'+id+'' ).siblings( ".multiselect" ).children( ".loading" ).show();
	$( '#'+id+'' ).siblings( ".multiselect" ).children( "ul" ).hide();
	
	setTimeout(function(){
		
		//Get value for search
		var filter = $( '#'+id+'' ).siblings( "input" ).val().toUpperCase();
		
		//Validate filter
		if (filter != '') {
			var options = $( '#'+id+'' ).siblings( ".multiselect" ).children( "ul" ).children( "li" );
			
			options.each(function( index ) {
				
				var value = $( this ).text().toUpperCase();
				
				if ( value.toUpperCase().indexOf(filter) > -1 ) {
					$( this ).show();
				}
				else {
					$( this ).hide();
				}
			});
		}
		else {
			var options = $( '#'+id+'' ).siblings( ".multiselect" ).children( "ul" ).children( "li" );
			
			options.each(function( index ) {
				$( this ).show();
			});
		}
		
		//Enabled Node multiselect
		$( '#'+id+'' ).siblings( ".multiselect" ).children( ".loading" ).hide();
		$( '#'+id+'' ).siblings( ".multiselect" ).children( "ul" ).show();
	}, 800);
	
}

/**
 * @function     agregarEmpresa
 * @author:      Danny Ramirez
 * @contact:     danny_ramirez@avansys.com.mx
 * @description: Agregar empresa disponible de lado derecho
 * @version:     1.0
 * @path call:   usuario/index.phtml
 * @copyright:   Avansys
 **/
function agregarEmpresa() {
	
	$( "#disponibles input:checked" ).each(function( index ) {
		$( this ).prop( "checked", false );
		$( this ).parent().clone().appendTo( "#seleccionados" );
		$( this ).parent().remove();
	});
}

/**
 * @function     quitarEmpresa
 * @author:      Danny Ramirez
 * @contact:     danny_ramirez@avansys.com.mx
 * @description: Remover empresa asignada de lado derecho
 * @version:     1.0
 * @path call:   usuario/index.phtml
 * @copyright:   Avansys
 **/
function quitarEmpresa() {
	
	$( "#seleccionados input:checked" ).each(function( index ) {
		$( this ).prop( "checked", false );
		$( this ).parent().clone().appendTo( "#disponibles" );
		$( this ).parent().remove();
	});
}
