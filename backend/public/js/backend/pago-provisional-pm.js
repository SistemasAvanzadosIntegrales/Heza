/**
 * @function     Document ready
 * @author:      Danny Ramirez
 * @contact:     roberto_ramirez@avansys.com.mx
 * @description: Funcion para cargar empresas en selector.
 * @version:     1.0
 * @path call:   pago-provisionalpm/index.phtml
 * @copyright:   Avansys
 **/
$(document).ready(function(){
	
	//Desactivar opciones
	$( '.filtros a' ).toggleClass('disabled', true);
	
	//funciones para la carga de empresas
	$( '#fempresa_id' ).chosen({ width:"250px" });
	
	//Cargar empresas al chosen de empresas.
	cargaEmpresas();
	
	//funciones para la carga de ejercicios
	$( '#fejercicio_id' ).chosen({ width:"250px" });
	
	var empresa = '';
	
	//Funcion para cargar ejercicios al seleccionar la empresa
	$( '#fempresa_id' ).on('change', function(evt, params) {
		
		empresa = params.selected;							//Asignar valor a empresa para utilizar despues
		$( '#id_empresa' ).val(params.selected);			//Asignar valor al id de la empresa
		$( '.filtros a' ).toggleClass('disabled', true);	//Bloquear botones de acción
		
		//Esconder todos los botones de acción
		$("#btnSincronizar").addClass('hide');
		$("#btnCongelar").addClass('hide');
		$("#btnAutorizacion").addClass('hide');
		
		$.ajax({
			type: "POST",
			url: "/pago-provisionalpm/obtenerejercicios",
			dataType: "json",
			data: { id_empresa : params.selected },
			success:function( data ){
				
				$( "#fejercicio_id option" ).remove();
				
				$.each(data, function (i, item) {
					$( '#fejercicio_id' ).append($('<option>', {
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
	});
	
	//Evento despues de seleccionar ejercicio
	$('#fejercicio_id').on('change', function(evt, params) {
		
		if( params.selected != 0 ){
			
			//Eliminar el evento anterior de click para que no se acumulen.
			$( '#btnSincronizar' ).unbind('click');
			
			//Esconder todos los botones de acción.
			$("#btnSincronizar").addClass('hide');
			$("#btnCongelar").addClass('hide');
			$("#btnAutorizacion").addClass('hide');
			
			//Asignar valor al id de ejercicio.
			$('#id_ejercicio').val(params.selected);
			
			//Remover clase de congelado y sin autorizar a las opciones.
			$( "a[id*='period']").removeClass('congelado');
			$( "a[id*='author']").removeClass('sinAutorizar');
			
			//Bloquear botones de acción
			$( '.filtros a' ).toggleClass('disabled', true);
			
			$.ajax({
				url: '/pago-provisionalpm/get-status-congelado',
				type: 'POST',
				data: {empresa   : empresa,
					   ejercicio : params.selected},
				dataType: 'JSON',
				success: function(respuesta){
					
					var congelar = '';
					var sincronizar = '';
					var congelados = respuesta;
					
					if ( respuesta != 12) {
						
						//Botón Congelar
						for ( i = 1; i <= respuesta; i++ ) {
							$( "#period-"+i+"").addClass('congelado');
						}
						
						//Botón Autorizar
						for ( i = (respuesta + 1); i <= 12; i++ ) {
							$( "#author-"+i+"").addClass('sinAutorizar');
						}
						
						congelar    = true;
						sincronizar = true;
					}
					else if (respuesta == 12) {
						congelar    = false;
						sincronizar = false;
					}
					else {
						congelar    = true;
						sincronizar = true;
					}
					
					$.ajax({
						url: '/pago-provisionalpm/get-status-autorizacion',
						type: 'POST',
						data: {empresa   : empresa,
							   ejercicio : params.selected},
						success: function(res){
							
							if (congelar && sincronizar && res == '' && congelados == 0) {
								// console.log('Congelar = mostrar, Sincronizar = mostrar, Autorizar = mostrar, congelados = 0');
								$("#btnCongelar").removeClass('hide');
								$("#btnSincronizar").removeClass('hide');
								$("#btnAutorizacion").addClass('hide');
							}
							else if (congelar && sincronizar && res == '' && congelados > 0) {
								// console.log('Congelar = mostrar, Sincronizar = mostrar, Autorizar = mostrar, congelados > 0');
								$("#btnCongelar").removeClass('hide');
								$("#btnSincronizar").removeClass('hide');
								$("#btnAutorizacion").removeClass('hide');
							}
							else if (congelar && sincronizar && res == 0) {
								// console.log('Congelar = ocultar, Sincronizar = mostrar, Autorizar = ocultar');
								$("#btnCongelar").addClass('hide');
								$("#btnSincronizar").removeClass('hide');
								$("#btnAutorizacion").addClass('hide');
							}
							else if (!congelar && !sincronizar && res == '') {
								// console.log('Congelar = ocultar, Sincronizar = ocultar, Autorizar = mostrar');
								$("#btnCongelar").addClass('hide');
								$("#btnSincronizar").addClass('hide');
								$("#btnAutorizacion").removeClass('hide');
							}
							else if (!congelar && !sincronizar && res == 0) {
								// console.log('Congelar = ocultar, Sincronizar = ocultar, Autorizar = ocultar');
								$("#btnCongelar").addClass('hide');
								$("#btnSincronizar").addClass('hide');
								$("#btnAutorizacion").addClass('hide');
							}
						}
					});
				}
			});
			
			//Desbloquear botones de acción.
			$('.filtros a').toggleClass('disabled', false);
			
			//Bloquear botones de acción cuando se hace una busqueda.
			$( '#btnSearch' ).click(function(e) {
				$('.filtros a').toggleClass('disabled', true);
			});
			
			//Click que activa la función de congelar.
			$( "a[id*='period']" ).click(function(e) {
				var periodo = $(this).data("periodo");
				var mes     = $(this).data("name");
				
				congelarPeriodos(empresa, params.selected, periodo, mes);
			});
			
			//Click que activa la función de congelar.
			$( "a[id*='author']" ).click(function(e) {
				var periodo = $(this).data("periodo");
				var mes     = $(this).data("name");
				$('.filtros a').toggleClass('disabled', true);
				enviarAutorizacion(empresa, params.selected, periodo, mes);
			});
			
			
			//Click que activa la función de sincronizar.
			$( '#btnSincronizar' ).click(function(e) {
				$('.filtros a').toggleClass('disabled', true);
				setDataContpaq();
			});
		}
	});
});

/**
 * @function     cargaEmpresas
 * @author:      Christian Murillo
 * @contact:     christian_murillo@avansys.com.mx
 * @description: Funcion para cargar empresas en selector.
 * @version:     1.0
 * @path call:   pago-provisionalpm/index.phtml
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
 * @function     cargaContent
 * @author:      Christian Murillo
 * @contact:     christian_murillo@avansys.com.mx
 * @description: Funcion para cargar contenido pagos provisionales.
 * @version:     1.0
 * @path call:   pago-provisionalpm/index.phtml
 * @copyright:   Avansys
 **/
function cargaContent ( formulario, urlImprimir, urlExportar ) {
	
	//Limpiar los campos hidden
	$(".impuestos").each(function( index ){
		if($(this).val() != ''){
			$(this).val('');
		}
	});
	
	var filtro = '';
	
	$( "#"+formulario+" :input" ).each(function(){
		if( this.id != '' && $( "#"+this.id ).val() != '' )
			filtro += "/"+this.name+"/"+$("#"+this.id).val();
	});
	
	$( "#btnImprimir" ).attr("href",urlImprimir+filtro);
	$( "#btnExportar" ).attr("href",urlExportar+filtro);
	
	var empresa   = $( '#id_empresa' ).val();
	var ejercicio = $( '#id_ejercicio' ).val();
	
	$( "#progressbar" ).removeClass( "hide" );
	$( "#pagoProvPmContent" ).addClass('hide');
	
	$.ajax({
		type: "POST",
		url: "/pago-provisionalpm/obtenerpagospm",
		data: { id_empresa : empresa, 
				id_ejercicio : ejercicio },
		success:function( data ){
			
			$( '#pagoProvPmContent' ).html(data);
			
			$( "#pagoProvPmContent" ).removeClass( "hide" );
			$( "#progressbar" ).addClass('hide');
			$('.filtros a').toggleClass('disabled', false);
			
			//Aplicar remanente
			$.ajax({
				type: "POST",
				url: "/pago-provisionalpm/obtener-remanente",
				data: { id_empresa : empresa, 
						id_ejercicio : ejercicio },
				success:function( data ){
					$( "#remanente" ).val(data);
				},
				error: function ( error ){
					_mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado al cargar pagos");
					$('.filtros a').toggleClass('disabled', false);
				}
			});
			
			//Status Compensaciones
			//Aplicar remanente
			$.ajax({
				type: "POST",
				url: "/pago-provisionalpm/obtener-compensacion-status",
				data: { id_empresa : empresa, 
						id_ejercicio : ejercicio },
				success:function( data ){
					if(data == 2){
						//Script de aplicaciones
						aplicar_impuesto();
					}
					else if (data == 3) {
						_mensaje("#_mensaje-1", "Estan congelados todos los periodos, por lo que no hay impuestos que aplicar.");
					}
					else {
						_mensaje("#_mensaje-1", "Faltan compensaciones por dar de alta antes de poder aplicar impuestos.");
					}
				},
				error: function ( error ){
					_mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado al cargar pagos");
					$('.filtros a').toggleClass('disabled', false);
				}
			});
		},
		error: function ( error ){
			
			_mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado al cargar pagos");
			$('.filtros a').toggleClass('disabled', false);
		}
	});
	
	$('.filtros a').toggleClass('disabled', false);
}

/**
 * @function     cargaEmpresas
 * @author:      Christian Murillo
 * @contact:     christian_murillo@avansys.com.mx
 * @description: Funcion para cargar empresas en selector.
 * @version:     1.0
 * @path call:   pago-provisionalpm/index.phtml
 * @copyright:   Avansys
 **/
function cargaEmpresas () {
	
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
 * @function     setDataContpaq
 * @author:      Danny Ramirez
 * @contact:     danny_ramirez@avansys.com.mx
 * @description: Función para sincronizar la base de datos de compac.
 * @version:     1.0
 * @path call:   pago-provisionalpm/index.phtml
 * @copyright:   Avansys
 **/
function setDataContpaq () {
	
	$( "#progressbar" ).removeClass( "hide" );
	$( "#pagoProvPmContent" ).addClass('hide');
	$('.filtros a').toggleClass('disabled', true);
	$('#fejercicio_id_chosen').addClass('inactive');
	$('#fempresa_id_chosen').addClass('inactive');
	
	var empresa = $('#id_empresa').val();
	var ejercicio = $('#id_ejercicio').val();
	
	$.ajax({
		
		type: "POST",
		url: "/pago-provisionalpm/setdatacontpaq",
		data: { empresa : empresa,
				ejercicio : ejercicio },
		success:function ( data ){
			
			_mensaje("#_mensaje-1",  data );
			$( "#progressbar" ).addClass('hide');
			$('.filtros a').toggleClass('disabled', false);
			$('#fejercicio_id_chosen').removeClass('inactive');
			$('#fempresa_id_chosen').removeClass('inactive');
			
		},
		error: function (error){
			_mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado");
			$('.filtros a').toggleClass('disabled', false);
		}
	});
}

/**
 * @function     congelarPeriodos
 * @author:      Danny Ramirez
 * @contact:     danny_ramirez@avansys.com.mx
 * @description: Función para sincronizar la base de datos de compac.
 * @version:     1.0
 * @path call:   pago-provisionalpm/index.phtml
 * @copyright:   Avansys
 **/
function congelarPeriodos (empresa, ejercicio, periodo, mes) {
	
	_confirmar('#_mensaje-1', 'La sincronización de Empresa, Ejercicio y el periodo '+mes+' hacia atras, ya no podra volver a realizarse, ¿Desea continuar?',
		function(){
			
			$("#es_mensaje-1").removeClass("hide");
			$("#texto_mensaje-1").addClass("hide");
			$("#si-999").addClass('hide');
			$("#no-999").addClass('hide');
			
			$.ajax({
				url: '/pago-provisionalpm/congelar-ejercicio',
				type: 'POST',
				data: {empresa   : empresa,
					   ejercicio : ejercicio,
					   periodo   : periodo},
				success: function(res){
					if(!isNaN(res)){
						_mensaje('#_mensaje-1', 'Se ha congelado correctamente el periodo.');
						
						//Remover clase de congelado y sin autorizar a las opciones.
						$( "a[id*='period']").removeClass('congelado');
						$( "a[id*='author']").removeClass('sinAutorizar');
						
						if (periodo < 12) {
							
							//Botón Congelar
							for ( i = 1; i <= periodo; i++ ) {
								$( "#period-"+i+"").addClass('congelado');
							}
							
							//Botón Autorizar
							for ( i = (periodo + 1); i <= 12; i++ ) {
								$( "#author-"+i+"").addClass('sinAutorizar');
							}
							
							$("#btnAutorizacion").removeClass('hide');
						}
						else if (periodo == 12) {
							$("#btnCongelar").addClass('hide');
							$("#btnSincronizar").addClass('hide');
							$("#btnAutorizacion").removeClass('hide');
						}
						
						$('.filtros a').toggleClass('disabled', false);
						
					}
					else {
						_mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado");
						$('.filtros a').toggleClass('disabled', false);
					}
					
					//Send data
					//get all input hidden
					var impuestos = [];
					$(".impuestos").each(function( index ){
						if($(this).val() != '' && $(this).val() != 0){
							var data = {};
							
							data.id    = $(this).attr('id');
							data.value = $(this).val();
							
							impuestos.push(data);
						}
					});
					
					$.ajax({
						url: '/pago-provisionalpm/guardar-aplicaciones',
						type: 'POST',
						data: {empresa   : empresa,
							   ejercicio : ejercicio,
							   periodo   : periodo,
							   data      : impuestos },
						success: function(res){
							_mensaje('#_mensaje-1', 'Se han guardado las aplicaciones correctamente.');
						}
					});
				}
			});
		});
	
	$('.filtros a').toggleClass('disabled', false);
}

/**
 * @function     enviarAutorizacion
 * @author:      Danny Ramirez
 * @contact:     danny_ramirez@avansys.com.mx
 * @description: Función para sincronizar la base de datos de compac.
 * @version:     1.0
 * @path call:   pago-provisionalpm/index.phtml
 * @copyright:   Avansys
 **/
function enviarAutorizacion (empresa, ejercicio, periodo, mes) {
	
	_confirmar('#_mensaje-1', 'Se enviara una notificación al administrador para validar la autorización de descongelar a partir del mes '+mes+', se le dara respuesta via Email de la resolución, ¿Desea continuar?',
		function(){
			
			$("#es_mensaje-1").removeClass("hide");
			$("#texto_mensaje-1").addClass("hide");
			$("#si-999").addClass('hide');
			$("#no-999").addClass('hide');
			
			$.ajax({
				url: '/pago-provisionalpm/enviar-autorizacion',
				type: 'POST',
				data: {empresa   : empresa,
					   ejercicio : ejercicio,
					   periodo   : periodo},
				success: function(res){
					
					_mensaje('#_mensaje-1', res);
					$( "#_mensaje-1" ).dialog("destroy");
					
					$("#btnAutorizacion").addClass('hide');
					$("#btnCongelar").addClass('hide');
					$('.filtros a').toggleClass('disabled', false);
				}
			});
		});
	
	$('.filtros a').toggleClass('disabled', false);
}

/**
 * aplicar_impuesto()
 **/
function aplicar_impuesto() {
	
	var $index = $("#impuestos").index();
	var column = $( "td:nth-child("+($index + 1)+")*[id]" );
	
	/**
	 * To follow a hierarchy
	 */
	//Classes
	var getClass2  = column.eq(2).hasClass( "_subsidio" );
	var getClass0  = column.eq(0).hasClass( "_subsidio" );
	var getClass4  = column.eq(4).hasClass( "_subsidio" );
	var getClass10 = column.eq(10).hasClass( "_subsidio" );
	var getClass3  = column.eq(3).hasClass( "_subsidio" );
	var getClass5  = column.eq(5).hasClass( "_subsidio" );
	var getClass6  = column.eq(6).hasClass( "_subsidio" );
	var getClass7  = column.eq(7).hasClass( "_subsidio" );
	var getClass8  = column.eq(8).hasClass( "_subsidio" );
	var getClass9  = column.eq(9).hasClass( "_subsidio" );
	var getClass1  = column.eq(1).hasClass( "_subsidio" );
	
	//Classes
	var getClass2_c  = column.eq(2).hasClass( "_compensacion" );
	var getClass0_c  = column.eq(0).hasClass( "_compensacion" );
	var getClass4_c  = column.eq(4).hasClass( "_compensacion" );
	var getClass10_c = column.eq(10).hasClass( "_compensacion" );
	var getClass3_c  = column.eq(3).hasClass( "_compensacion" );
	var getClass5_c  = column.eq(5).hasClass( "_compensacion" );
	var getClass6_c  = column.eq(6).hasClass( "_compensacion" );
	var getClass7_c  = column.eq(7).hasClass( "_compensacion" );
	var getClass8_c  = column.eq(8).hasClass( "_compensacion" );
	var getClass9_c  = column.eq(9).hasClass( "_compensacion" );
	var getClass1_c  = column.eq(1).hasClass( "_compensacion" );
	
	_mensajeFuncion("#_mensaje-1", "Tienes impuestos pendientes por aplicar.", scrolldown);
	
	//Retención de salarios
	if ( !getClass2 ) {
		if(column.eq(2).text() != 0.00) {
			getWindowApplication(column.eq(2), column.eq(2).text(), 1);
		}
		else {
			column.eq(2).addClass('_subsidio');
			aplicar_impuesto();
		}
	}
	//ISR
	else if ( !getClass0 ) {
		if (column.eq(0).text() != 0.00) {
			getWindowApplication(column.eq(0), column.eq(0).text(), 1);
		}
		else {
			column.eq(0).addClass('_subsidio');
			aplicar_impuesto();
		}
	}
	//Retencion de asimilados
	else if ( !getClass4 ) {
		if (column.eq(4).text() != 0.00) {
			getWindowApplication(column.eq(4), column.eq(4).text(), 1);
		}
		else {
			column.eq(4).addClass('_subsidio');
			aplicar_impuesto();
		}
	}
	//Retención ISR de arrendamiento
	else if ( !getClass10 ) {
		if (column.eq(10).text() != 0.00) {
			getWindowApplication(column.eq(10), column.eq(10).text(), 1);
		}
		else {
			column.eq(10).addClass('_subsidio');
			aplicar_impuesto();
		}
	}
	//Retención ISR de honorarios
	else if ( !getClass3 ) {
		if (column.eq(3).text() != 0.00) {
			getWindowApplication(column.eq(3), column.eq(3).text(), 1);
		}
		else {
			column.eq(3).addClass('_subsidio');
			aplicar_impuesto();
		}
	}
	//Retención de dividendos
	else if ( !getClass5 ) {
		if (column.eq(5).text() != 0.00) {
			getWindowApplication(column.eq(5), column.eq(5).text(), 1);
		}
		else {
			column.eq(5).addClass('_subsidio');
			aplicar_impuesto();
		}
	}
	//Retención de intereses
	else if ( !getClass6 ) {
		if (column.eq(6).text() != 0.00) {
			getWindowApplication(column.eq(6), column.eq(6).text(), 1);
		}
		else {
			column.eq(6).addClass('_subsidio');
			aplicar_impuesto();
		}
	}
	//Retención de pagos al extranjero
	else if ( !getClass7 ) {
		if (column.eq(7).text() != 0.00) {
			getWindowApplication(column.eq(7), column.eq(7).text(), 1);
		}
		else {
			column.eq(7).addClass('_subsidio');
			aplicar_impuesto();
		}
	}
	//Retención de venta de acciones
	else if ( !getClass8 ) {
		if (column.eq(8).text() != 0.00) {
			getWindowApplication(column.eq(8), column.eq(8).text(), 1);
		}
		else {
			column.eq(8).addClass('_subsidio');
			aplicar_impuesto();
		}
	}
	//Retención de venta de partes sociales
	else if ( !getClass9 ) {
		if (column.eq(9).text() != 0.00) {
			getWindowApplication(column.eq(9), column.eq(9).text(), 1);
		}
		else {
			column.eq(9).addClass('_subsidio');
			aplicar_impuesto();
		}
	}
	//impuesto IVA
	else if ( !getClass1 ) {
		if (column.eq(1).text() != 0.00) {
			getWindowApplication(column.eq(1), column.eq(1).text(), 1);
		}
		else {
			column.eq(1).addClass('_subsidio');
			aplicar_impuesto();
		}
	}
	
	//Compensaciones
	
	//Retención de salarios
	else if ( !getClass2_c ) {
		if(column.eq(2).text() != 0.00) {
			getWindowApplication(column.eq(2), column.eq(2).text(), 2);
		}
		else {
			column.eq(2).addClass('_compensacion');
			aplicar_impuesto();
		}
	}
	//ISR
	else if ( !getClass0_c ) {
		if (column.eq(0).text() != 0.00) {
			getWindowApplication(column.eq(0), column.eq(0).text(), 2);
		}
		else {
			column.eq(0).addClass('_compensacion');
			aplicar_impuesto();
		}
	}
	//Retencion de asimilados
	else if ( !getClass4_c ) {
		if (column.eq(4).text() != 0.00) {
			getWindowApplication(column.eq(4), column.eq(4).text(), 2);
		}
		else {
			column.eq(4).addClass('_compensacion');
			aplicar_impuesto();
		}
	}
	//Retención ISR de arrendamiento
	else if ( !getClass10_c ) {
		if (column.eq(10).text() != 0.00) {
			getWindowApplication(column.eq(10), column.eq(10).text(), 2);
		}
		else {
			column.eq(10).addClass('_compensacion');
			aplicar_impuesto();
		}
	}
	//Retención ISR de honorarios
	else if ( !getClass3_c ) {
		if (column.eq(3).text() != 0.00) {
			getWindowApplication(column.eq(3), column.eq(3).text(), 2);
		}
		else {
			column.eq(3).addClass('_compensacion');
			aplicar_impuesto();
		}
	}
	//Retención de dividendos
	else if ( !getClass5_c ) {
		if (column.eq(5).text() != 0.00) {
			getWindowApplication(column.eq(5), column.eq(5).text(), 2);
		}
		else {
			column.eq(5).addClass('_compensacion');
			aplicar_impuesto();
		}
	}
	//Retención de intereses
	else if ( !getClass6_c ) {
		if (column.eq(6).text() != 0.00) {
			getWindowApplication(column.eq(6), column.eq(6).text(), 2);
		}
		else {
			column.eq(6).addClass('_compensacion');
			aplicar_impuesto();
		}
	}
	//Retención de pagos al extranjero
	else if ( !getClass7_c ) {
		if (column.eq(7).text() != 0.00) {
			getWindowApplication(column.eq(7), column.eq(7).text(), 2);
		}
		else {
			column.eq(7).addClass('_compensacion');
			aplicar_impuesto();
		}
	}
	//Retención de venta de acciones
	else if ( !getClass8_c ) {
		if (column.eq(8).text() != 0.00) {
			getWindowApplication(column.eq(8), column.eq(8).text(), 2);
		}
		else {
			column.eq(8).addClass('_compensacion');
			aplicar_impuesto();
		}
	}
	//Retención de venta de partes sociales
	else if ( !getClass9_c ) {
		if (column.eq(9).text() != 0.00) {
			getWindowApplication(column.eq(9), column.eq(9).text(), 2);
		}
		else {
			column.eq(9).addClass('_compensacion');
			aplicar_impuesto();
		}
	}
	//impuesto IVA
	else if ( !getClass1_c ) {
		if (column.eq(1).text() != 0.00) {
			getWindowApplication(column.eq(1), column.eq(1).text(), 2);
		}
		else {
			column.eq(1).addClass('_compensacion');
			aplicar_impuesto();
		}
	}
}

/**
 * aplicar_impuesto()
 **/
function getWindowApplication(impuesto, monto, tipo) {
	
	impuesto.css("color", "white");
	impuesto.css("background", "#5bc0de");
	impuesto.css("cursor", "pointer");
	
	impuesto.click(function(e) {
		
		$(document).scrollTop(0);
		$("body").css("cursor", "wait");
		var remanente = $( "#remanente" ).val();
		
		$.ajax({
			type: "POST",
			url: "/pago-provisionalpm/ver-aplicacion",
			data: {
				impuesto  : impuesto.attr('id'),
				monto     : monto,
				tipo      : tipo,
				remanente : remanente
			},
			success: function(html){
				
				$("#_dialogo-2").html(html);
				$("body").css("cursor", "default");
				
				$("#_dialogo-2").dialog({
					width: "900"
					,title: "Liquidar impuesto"
					,resizable: false
					,draggable: false
					,position: "center"
					,modal: true
					,buttons: [
						{
							id: "aceptar-1"
							,text: "Aceptar"
							,class: "btn btn-success"
							,click: function(){
								
								$("body").css("cursor", "wait");
								
								//Variables
								var impuesto_id = impuesto.attr('id');
								var type        = ''; 
								var value       = $.trim($( "#monto" ).val());
								value           = value.replace(',', '');		//Eliminando las commas.
								monto           = monto.replace(',', '');		//Eliminando las commas.
								
								//Tipo de impuesto
								if(tipo == 1) {
									type = '_subsidio';
								}
								else {
									type = '_compensacion';
								}
								
								//Obtener ID hidden del impuesto
								var $hidden = impuesto_id+type;
								// console.log($hidden);
								
								if (parseFloat(value) > parseFloat(remanente)) {
									_mensaje("#_mensaje-1", "No hay suficiente remanente para cubrir este impuesto.");
								}
								else if (value == '') {
									_mensaje("#_mensaje-1", "El monto aplicado esta vacío por favor insertar un valor correcto.");
								}
								else if (parseFloat(value) > parseFloat(monto)) {
									_mensaje("#_mensaje-1", "El monto aplicado es mayor al monto original.");
								}
								else if (parseFloat(value) == parseFloat(monto)) {
									//Actualizar el campo de impuesto
									impuesto.text('0.00');
									
									//Actualizar el remanente
									var remanente_after = remanente - value;
									remanente_after = remanente_after.toFixed(2);
									$( "#remanente" ).val(remanente_after);
									
									//Guardar valor en el hidden
									$( "#"+$hidden+"" ).val(value);
									
									impuesto.css("color", "white");
									impuesto.css("background", "#5cb85c");
									impuesto.css("cursor", "default");
									impuesto.addClass(type);
									
									//Desactivar el click
									impuesto.unbind("click");
									
									//Mensajes
									// _mensaje("#_mensaje-1", "Se liquido el impuesto correctamente.");
									_mensajeFuncion("#_mensaje-1", "Se liquido el impuesto correctamente.", scrolldown);
									
									$("#_dialogo-2").dialog("close");
								}
								else if (parseFloat(value) < parseFloat(monto)) {
									var impuesto_after  = monto - value;
									impuesto_after = impuesto_after.toFixed(2);
									var remanente_after = remanente - value;
									remanente_after = remanente_after.toFixed(2);
									
									//Actualizar el campo de impuesto
									impuesto.text(impuesto_after);
									
									//Actualizar el remanente
									$( "#remanente" ).val(remanente_after);
									
									//Guardar valor en el hidden
									$( "#"+$hidden+"" ).val(value);
									
									impuesto.css("color", "white");
									impuesto.css("background", "#f0ad4e");
									impuesto.css("cursor", "default");
									impuesto.addClass(type);
									
									//Desactivar el click
									impuesto.unbind("click");
									
									//Mensajes
									// _mensaje("#_mensaje-1", "Se guardo el impuesto correctamente.");
									_mensajeFuncion("#_mensaje-1", "Se guardo el impuesto correctamente.", scrolldown);
									$("#_dialogo-2").dialog("close");
								}
								else {
									_mensaje("#_mensaje-1", "Ninguna de las opciones anteriores.");
								}
								
								$("body").css("cursor", "default");
								aplicar_impuesto();
							}
						}
						,{
							id: "cancelar-1"
							,text: "Cerrar"
							,class: "btn btn-danger"
							,click: function(){
								$("#_dialogo-2").dialog("close");
							}
						}
					]
				})
			},
			error: function(respuesta) {
				_mensaje("#_mensaje-1", "Ocurri&oacute; un error al tratar de abrir la ventana de "+impuesto.innerHTML+" , int&eacute;ntelo de nuevo");
			}
		});
	});
}

/**
 * @function     scrolldown
 * @author:      
 * @contact:     
 * @description: Funcion para cargar empresas en selector.
 * @version:     1.0
 * @path call:   subsidio-empleo/index.phtml
 * @copyright:   Avansys
 **/
function scrolldown () {
	$("html, body").animate({ scrollTop: $(document).height()-$(window).height() });
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
	
	var file = "/pago-provisionalpm/exportar/empresa_id/"+empresa+"/ejercicio_id/"+ejercicio+"";
	
	window.open(file);
}