<?php
//require_once ('auth.php');
//require_once ('core/core.php');
//ConnectDB();
//exit;
//WatchDog();
//$gsession = GetCurrentGSession();
//$gsession_id = $gsession->gsession_id;
//$current_user_id = GetCurrentUserId();
//$current_user_name = GetUserName($current_user_id);
//$current_chat_room = "gs_$gsession_id";
$bid_delta = 10;

global $ceil_color_ar;
$ceil_color_ar = array('purple', 'teal');
reset($ceil_color_ar);

$ceil_color_ind = 1;
$ceil_tmpl1 = '<section id="c%i%" class="%color% right"></section>';
$ceil_tmpl_top = '<section id="sc%i%" class="%color% right"><div id="c%i%" style="width:100%;height:95%"></div><div id=up%i% style="width:100%;height:5%"></div></section>';
$ceil_tmpl_left = '<section id="sc%i%" class="%color% right"><div id="c%i%" class="right" style="width:95%;height:100%"></div><div id=up%i% class="right" style="width:5%;height:100%"></div></section>';
$ceil_tmpl_right = '<section id="sc%i%" class="%color% right"><div id=up%i% class="right" style="width:5%;height:100%"></div><div id="c%i%" class="right" style="width:95%;height:100%"></div></section>';
$ceil_tmpl_bottom = '<section id="sc%i%" class="%color% right"><div id=up%i% style="width:100%;height:5%"></div><div id="c%i%" style="width:100%;height:95%"></div></section>';
/*
$ceil_tmpl1 = '
        <section id="c%i%" class="%color% right">
		<div id="pic%i%" style="height: 70%;">
		%FGROUP_ID%.%NAME%</br>%FCOST%
		</div>
		<div id="pzc%i%">
		%USERLIST%
		</div>
	</section>';
$tpl_ceil_user = '<div id=uc%ACT_ORDER% class="pl us_c%ACT_ORDER%"></div>';

$ceil_tmpl = '
        <section id="c%i%" class="%color% right">
		<div style="height: 70%;">
		C%i%
		</div>
		<div id="pzc%i%">
		<div class="pl red"></div>
		<div class="pl yellow"></div>
		<div class="pl green"></div>
		<div class="pl blue"></div>
		<div class="pl white"></div>
		</div>
	</section>';
*/
function GetNextColor() {
	global $ceil_color_ar;
	$color = current($ceil_color_ar);
	if (next($ceil_color_ar) == FALSE) {
		reset($ceil_color_ar);
		//$color=next($ceil_color_ar);
	}
	return $color;
}
?>

<script type="text/javascript">
	//refresh time
	var rtime=3000;
	//lastupdated
	var lastupdated='';
	var gturn=0;
	var refresh_running=false;
	var first_refresh=true;
	var gameover=false;
<?php if ($G_MODE == G_MODE_PLAY) { ?>
	function DoAction() {
	$.ajaxSetup({cache: false});
	$.get("do_maketurn.php?gs_id=<?php echo $gsession_id; ?>&user_id=<?php echo $current_user_id; ?>"
		, function(data){
		$("#console").append(data);
		$.get("board.php?gs_id=<?php echo $gsession_id; ?>&user_id=<?php echo $current_user_id; ?>"
	, function(data) {
	$("#board").html(data);
	});
	});

	$('#console').append("</br>Dice</br>");
	}
	
	function DoAction1() {
	$.ajaxSetup({cache: false});
	$.get("do_maketurn.php?gs_id=<?php echo $gsession_id; ?>&user_id=<?php echo $current_user_id; ?>"
		, function(data){
		if ($("#console").children().length > 8) {
			$("#console").html('');
		}
		$("#console").append(data);
		AutoRefresh();

	});
	}
	
	function DoBidAuction(auct_id) {
	$.ajaxSetup({cache: false});
	$.get("do_makebid.php?gs_id=<?php echo $gsession_id; ?>&user_id=<?php echo $current_user_id; ?>&bid=<?php echo $bid_delta; ?>&auct_id="+auct_id
		, function(data){
		if ($("#console").children().length > 8) {
			$("#console").html('');
		}
		$("#console").append(data);
		AutoRefresh();

	});
	}
		
	function DoLeaveAuction(auct_id) {
	$.ajaxSetup({cache: false});
	$.get("do_auctionleave.php?gs_id=<?php echo $gsession_id; ?>&user_id=<?php echo $current_user_id; ?>&auct_id="+auct_id
		, function(data){
		if ($("#console").children().length > 8) {
			$("#console").html('');
		}
		$("#console").append(data);
		AutoRefresh();

	});
	}
	var auction_field_id=0;
	
	function DoOpenAuctionForm(field_id) {
		auction_field_id=field_id;
		$( ".validateTips" ).text("");
		$( "#dialog_auction_start" ).dialog( "open" );
	}
	
	function DoOpenAuction(bid) {
		field_id=auction_field_id;
	$.ajaxSetup({cache: false});
	$.get("do_auctionopen.php?gs_id=<?php echo $gsession_id; ?>&user_id=<?php echo $current_user_id; ?>&field_id="+field_id+"&bid="+bid
		, function(data){
		if ($("#console").children().length > 8) {
			$("#console").html('');
		}
		$("#console").append(data);
		AutoRefresh();

	});
	}

	function DoJoinAuction(auct_id) {
	$.ajaxSetup({cache: false});
	$.get("do_auctionjoin.php?gs_id=<?php echo $gsession_id; ?>&user_id=<?php echo $current_user_id; ?>&auct_id="+auct_id
		, function(data){
		if ($("#console").children().length > 8) {
			$("#console").html('');
		}
		$("#console").append(data);
		AutoRefresh();

	});
	}

	function DoUpFGroup(fgroup_id) {
	$.ajaxSetup({cache: false});
	$.get("do_changefgroup.php?gs_id=<?php echo $gsession_id; ?>&user_id=<?php echo $current_user_id; ?>&action=fgroup_up&fgroup_id="+fgroup_id
		, function(data){
		if ($("#console").children().length > 8) {
			$("#console").html('');
		}
		$("#console").append(data);
		AutoRefresh();

	});
	}
	function DoDownFGroup(fgroup_id) {
	$.ajaxSetup({cache: false});
	$.get("do_changefgroup.php?gs_id=<?php echo $gsession_id; ?>&user_id=<?php echo $current_user_id; ?>&action=fgroup_down&fgroup_id="+fgroup_id
		, function(data){
		if ($("#console").children().length > 8) {
			$("#console").html('');
		}
		$("#console").append(data);
		AutoRefresh();

	});
	}
	
	function DoOpenDeal(opponent_user_id, payment, hpset, opset) {
    /*hpset=[];
	if (hpset.length ==0) {
		hpset[0]="1";
		hpset[1]="2";
	}*/
	$.ajaxSetup({cache: false});
	$.get("do_deal.php", {gs_id:"<?php echo $gsession_id ?>", user_id: "<?php echo $current_user_id; ?>", action: "opendeal", deal_opponent_user_id: opponent_user_id, deal_payment: payment, 'hps[]': hpset, 'ops[]': opset }
		, function(data){
		if ($("#console").children().length > 8) {
			$("#console").html('');
		}
		$("#console").append(data);
		AutoRefresh();

	});
	}

	function DoCancelDeal(deal_id) {
	$.ajaxSetup({cache: false});
	$.get("do_deal.php", {gs_id:"<?php echo $gsession_id ?>", user_id: "<?php echo $current_user_id; ?>", action: "canceldeal", deal_id: deal_id }
		, function(data){
		if ($("#console").children().length > 8) {
			$("#console").html('');
		}
		$("#console").append(data);
		AutoRefresh();

	});
	}

	function DoAcceptDeal(deal_id) {
	$.ajaxSetup({cache: false});
	$.get("do_deal.php", {gs_id:"<?php echo $gsession_id ?>", user_id: "<?php echo $current_user_id; ?>", action: "acceptdeal", deal_id: deal_id }
		, function(data){
		if ($("#console").children().length > 8) {
			$("#console").html('');
		}
		$("#console").append(data);
		AutoRefresh();

	});
	}

	function DoRejectDeal(deal_id) {
	$.ajaxSetup({cache: false});
	$.get("do_deal.php", {gs_id:"<?php echo $gsession_id ?>", user_id: "<?php echo $current_user_id; ?>", action: "rejectdeal", deal_id: deal_id }
		, function(data){
		if ($("#console").children().length > 8) {
			$("#console").html('');
		}
		$("#console").append(data);
		AutoRefresh();

	});
	}
<?php } ?>
	function AutoRefresh(){
		if (!refresh_running) {
			refresh_running = true;
	//	if ($( "#dialog_loading" ).dialog( "isOpen" )) {$( "#progressbar" ).progressbar( "option", "value", 30 );}
		$.getJSON('boardjson.php?gmode=<?php echo $G_MODE; ?>&gs_id=<?php echo $gsession_id; ?>&user_id=<?php echo $current_user_id; ?>&lastupdated='+lastupdated, function(data) {	
			if (first_refresh) {
				first_refresh=false;
	            $.unblockUI();
			}
		//var active = $( "#auctions" ).accordion( "option", "active" );
	//	if ($( "#dialog_loading" ).dialog( "isOpen" )) {$( "#progressbar" ).progressbar( "option", "value", 60 );}
	$.each(data, function(key, val) {
		if (key=='lastupdated') {lastupdated = val;}
		if (key=="gturn") { gturn=val;} 
		if ((key=="gstatus")&&("2"==val)) {
			//$.blockUI({ message: '<h1>Game over</h1>' });
			if (!gameover) {
             	$('#board').block({ 
		                message: '<h1>Game over</h1>', 
		                css: { border: '3px solid #a00',
		                	   cursor: null } 
	            }); 
	            gameover=true;
            }
		}
        var txt = new String(key);
        if (key=="refreshtimeout") {
							if (rtime != val) {
								rtime = val;
								clearInterval(t);
								t = setInterval(function() {
									AutoRefresh();
								}, rtime);
							}
		} else if (txt.substr(0,6)=="log_id"){
			if ($("#"+key).length) {
				$("#"+key).replaceWith(val);
			} else {
				$("#actlog").prepend(val);
			}
		} else if (txt.substr(0,6)=="msg_id") {
			if ($("#"+key).length) {
				$("#"+key).replaceWith(val);
			} else {
				$("#gmsgbox").prepend(val);
			}
		} else if (txt.substr(0,12)=='property_set'){
			if (!$("#dialog_deal_start").dialog( "isOpen" )) {
				$("#"+key).html(val);
                //DealDialogPropertyRefresh();
			}
		} else if ((txt.substr(0,7)=="deallot") || (txt.substr(0,7)=="auctlot")){
			if ($("#"+key).length) {
				$("#"+key).replaceWith(val);
			} else {
				$("#auctbox").prepend(val);
			}
		} else {
			$("#"+key).html(val);
		}
	});

	//	if ($( "#dialog_loading" ).dialog( "isOpen" )) {$( "#progressbar" ).progressbar( "option", "value", 100 );}
    // $( "#auctions" ).accordion({ active: active }); 
    //	if ($( "#dialog_loading" ).dialog( "isOpen" )) { $( "#dialog_loading" ).dialog( "close" );}
	});
			refresh_running = false;
	}	
	}


	var t = setInterval(function(){
				AutoRefresh();
	},rtime);

	//BOT

/**/
		var bot = false;
		function SwitchBot() {
			bot = !bot;
			if (bot) {
				$("#bot").html("Bot!");
			} else {
				$("#bot").html("");
			}
			
		}

		var t2 = setInterval(function() {
			if (bot) {
				DoAction1();
			}
		}, 3500);
		

/**/
		//$(function() {
		//   $( "#auctions" ).accordion();
	    //});
	//	function DoStartDeal() {
	//		$("#dialog_deal_start").dialog('open');
	//	}
	//$(function() {
	//	 $("#dialog_deal_start").dialog({ autoOpen: false });
	//});
	$(function() {
		$.blockUI({ message: '<h1><img src="images/busy.gif" /> Loading...</h1>' });
		AutoRefresh();
		
	});
</script>
<?php if ($G_MODE == G_MODE_PLAY) { ?>
<script>


	$(function() {
		$( "#create-deal" )
			.button()
			.click(function() {
                $('#opponentcombobox-input').focus().val('');
               	$(".opponent_property_sets").addClass("hidden");
                $( ".validateTips" ).text("");
				$( "#dialog_deal_start" ).dialog( "open" );
			});
		// a workaround for a flaw in the demo system (http://dev.jqueryui.com/ticket/4375), ignore!
		//$( "#dialog:ui-dialog" ).dialog( "destroy" );
		
		var opponent = $( "#opponentcombobox" ),
			opponentcash = $( "#opponentcash" ),
			ownercash = $( "#ownercash" ),
			//password = $( "#password" ),
			allFields = $( [] ).add( opponent ).add( opponentcash ).add( ownercash ),
			tips = $( ".validateTips" );

		var bid = $( "#bid" );
			
		function updateTips( t ) {
			tips
				.text( t )
				.addClass( "ui-state-highlight" );
			setTimeout(function() {
				tips.removeClass( "ui-state-highlight", 1500 );
			}, 500 );
		}

		function checkNumber(o, t) {
			var s=o.val();
			//var n=parseFloat(s);
			//c1=!isNaN(parseFloat(s));
			//c2=isFinite(s);
			if  ($.trim(s)=="") {
				return true;
			}
		  if (!(!isNaN(parseFloat(s)) && isFinite(s) && parseFloat(s)>0)) {
		  	o.addClass( "ui-state-error" );
				updateTips( t+" is not a positive number" );
		  	return false;
		  };
		  return true;
		}

		function checkSelected(o, t) {
			
		  if (o.val().length==0) {
		  	o.addClass( "ui-state-error" );
				updateTips( t+" must be selected" );
		  	return false;
		  };
		  return true;
		}

		function checkSingle(o1,o2, t) {
			
		  if ((o1.val().length>0)&&(o2.val().length>0)) {
		  	o1.addClass( "ui-state-error" );
		  	o2.addClass( "ui-state-error" );
				updateTips( t );
		  	return false;
		  };
		  return true;
		}

		function checkLength( o, n, min, max ) {
			if ( o.val().length > max || o.val().length < min ) {
				o.addClass( "ui-state-error" );
				updateTips( "Length of " + n + " must be between " +
					min + " and " + max + "." );
				return false;
			} else {
				return true;
			}
		}

		function checkRegexp( o, regexp, t, canempty ) {
			if (canempty && ($.trim(o.val())=="")) {
				return true;
			}
			if ( !( regexp.test( o.val() ) ) ) {
				o.addClass( "ui-state-error" );
				updateTips( t );
				return false;
			} else {
				return true;
			}
		}
		
		$( "#dialog_deal_start" ).dialog({
			autoOpen: false,
			height: 400,
			width: 450,
			modal: true,
			buttons: {
				"Create deal": function() {
					var bValid = true;
					allFields.removeClass( "ui-state-error" );

					//bValid = bValid && checkLength( name, "username", 3, 16 );
					//bValid = bValid && checkLength( email, "email", 6, 80 );
					//bValid = bValid && checkLength( password, "password", 5, 16 );
					bValid = bValid && checkSelected( opponent, "Opponent");
					bValid = bValid && checkSingle( ownercash, opponentcash, "Only one cash field can be selected. Clean one of it.");
					bValid = bValid && checkRegexp( ownercash, /^([0-9])+$/i, "Cash field only allow: 0-9", true );
					bValid = bValid && checkRegexp( opponentcash, /^([0-9])+$/i, "Cash field only allow: 0-9", true );
					bValid = bValid && checkNumber( ownercash, "Cash value for owner");
					bValid = bValid && checkNumber( opponentcash, "Cash value for opponent");


					//bValid = bValid && checkRegexp( name, /^[a-z]([0-9a-z_])+$/i, "Username may consist of a-z, 0-9, underscores, begin with a letter." );
					// From jquery.validate.js (by joern), contributed by Scott Gonzalez: http://projects.scottsplayground.com/email_address_validation/
					//bValid = bValid && checkRegexp( email, /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i, "eg. ui@jquery.com" );
					//bValid = bValid && checkRegexp( password, /^([0-9a-zA-Z])+$/, "Password field only allow : a-z 0-9" );

					if ( bValid ) {
						//$( "#users tbody" ).append( "<tr>" +
						//	"<td>" + name.val() + "</td>" + 
						//	"<td>" + email.val() + "</td>" + 
						//	"<td>" + password.val() + "</td>" +
						//"</tr>" ); 
						var hps = [], ops = [];
                        $("#property_set_owner input:checkbox:checked").each(function(){
				        	//hps[hps.length]=$(this).attr('id');
				        	//chk_deal_field_id<> 
                        	var s=$(this).attr('id');
                            hps[hps.length]=s.substring(17,s.length);
					    });
                        $(".opponent_property_sets input:checkbox:checked").each(function(){
				        	//ops[ops.length]=$(this).attr('id');
                        	var s=$(this).attr('id');
                            ops[ops.length]=s.substring(17,s.length);

					    });

					    if (((ops.length>0) || (opponentcash.val()>0)) && ((hps.length>0) || (ownercash.val()>0))){
					    	DoOpenDeal(opponent.val(),ownercash.val()>0?ownercash.val():-(opponentcash.val()), hps, ops);
							$( this ).dialog( "close" );
					    } else {
					    	opponentcash.addClass( "ui-state-error" );
					    	ownercash.addClass( "ui-state-error" );
							updateTips( "Select what do you want to receive or give to deal participant" );
					    }
                        
					}
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			},
			close: function() {
				allFields.val( "" ).removeClass( "ui-state-error" );
			}
		});
		
		$( "#dialog_auction_start" ).dialog({
			autoOpen: false,
			height: 200,
			width: 350,
			modal: true,
			buttons: {
				"Create auction": function() {
					var bValid = true;
					allFields.removeClass( "ui-state-error" );

					bValid = bValid && checkRegexp( bid, /^([0-9])+$/i, "Bid field only allow: 0-9", false );
					bValid = bValid && checkNumber( bid, "Bid");
					
					if ( bValid ) {

					    	DoOpenAuction(bid.val());
							$( this ).dialog( "close" );
					   
					}
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			},
			close: function() {
				bid.val( "" ).removeClass( "ui-state-error" );
			}
		});

	});


	(function( $ ) {
		$.widget( "ui.combobox", {
			_create: function() {
				var input,
					self = this,
					select = this.element.hide(),
					selected = select.children( ":selected" ),
					value = selected.val() ? selected.text() : "",
					wrapper = this.wrapper = $( "<span>" )
						.addClass( "ui-combobox" )
						.insertAfter( select );

				input = $( "<input>" )
					.appendTo( wrapper )
					.val( value )
					.addClass( "ui-state-default ui-combobox-input" )
					.autocomplete({
						delay: 0,
						minLength: 0,
						source: function( request, response ) {
							var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
							response( select.children( "option" ).map(function() {
								var text = $( this ).text();
								if ( this.value && ( !request.term || matcher.test(text) ) )
									return {
										label: text.replace(
											new RegExp(
												"(?![^&;]+;)(?!<[^<>]*)(" +
												$.ui.autocomplete.escapeRegex(request.term) +
												")(?![^<>]*>)(?![^&;]+;)", "gi"
											), "<strong>$1</strong>" ),
										value: text,
										option: this
									};
							}) );
						},
						select: function( event, ui ) {
							ui.item.option.selected = true;
							self._trigger( "selected", event, {
								item: ui.item.option
							});
                            if (ui.item) {
                             // ui.item.value 
                             // ui.item.id 
                             	$(".opponent_property_sets").addClass("hidden");
						        $("#property_set_user_id"+ui.item.option.value).removeClass("hidden");
						        //$("#user_id"+ui.item.option.value+"_property_set").addClass("hidden");
                            }
						},
						change: function( event, ui ) {
							if ( !ui.item ) {
								var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( $(this).val() ) + "$", "i" ),
									valid = false;
								select.children( "option" ).each(function() {
									if ( $( this ).text().match( matcher ) ) {
										this.selected = valid = true;
										return false;
									}
								});
								if ( !valid ) {
									// remove invalid value, as it didn't match anything
									$( this ).val( "" );
									select.val( "" );
									input.data( "autocomplete" ).term = "";
									return false;
								}
							}
						}
					})
					.addClass( "ui-widget ui-widget-content ui-corner-left" );

				input.data( "autocomplete" )._renderItem = function( ul, item ) {
					return $( "<li></li>" )
						.data( "item.autocomplete", item )
						.append( "<a>" + item.label + "</a>" )
						.appendTo( ul );
				};

				$( "<a>" )
					.attr( "tabIndex", -1 )
					.attr( "title", "Show All Items" )
					.appendTo( wrapper )
					.button({
						icons: {
							primary: "ui-icon-triangle-1-s"
						},
						text: false
					})
					.removeClass( "ui-corner-all" )
					.addClass( "ui-corner-right ui-combobox-toggle" )
					.click(function() {
						// close if already visible
						if ( input.autocomplete( "widget" ).is( ":visible" ) ) {
							input.autocomplete( "close" );
							return;
						}

						// work around a bug (likely same cause as #5265)
						$( this ).blur();

						// pass empty string as value to search for, displaying all results
						input.autocomplete( "search", "" );
						input.focus();
					});
				input.attr( 'id', $(select).attr( 'id' )+'-input' );
			},

			destroy: function() {
				this.wrapper.remove();
				this.element.show();
				$.Widget.prototype.destroy.call( this );
			}
		});
	})( jQuery );

	$(function() {
		$( "#opponentcombobox" ).combobox();
		//$( "#toggle" ).click(function() {
		//	$( "#opponentcombobox" ).toggle();
		//});
	});

	/*$(function() {
		$( "#progressbar" ).progressbar({
			value: 10
		});
		$( "#dialog_loading" ).dialog({
			height: 140,
			modal: true
		});
	});*/
	function DealDialogPropertyRefresh() {
		var noIcon = {primary: null, secondary: null};
		//var withIconOff = {primary: 'ui-icon-radio-on', secondary: null};
		var withIcon = {primary: 'ui-icon-check', secondary: null};
		$('#property_set_owner input:checkbox').click(function(e) {
		    if ($(e.target).button("option", "icons").primary == null) {
		        $(e.target).button("option", "icons", withIcon).button('refresh');
		    } else {
		        $(e.target).button("option", "icons", noIcon).button('refresh');
		        //$(e.target).button("option", "icons", withIconOff).button('refresh');
		    }
		});
		$('#property_set_owner input:checkbox:checked').button({icons: withIcon});
		$('#property_set_owner input:checkbox:not(:checked)').button({icons: noIcon});
		//$('#owner_property_set input:checkbox:not(:checked)').button({icons: withIconOff});
		//$('#owner_property_set').buttonset();
		//$('#check1').button();
		//$('#check2').button();
		//$('#check3').button();

		//getter
		//var icons = $( "#check1" ).button( "option", "icons" );
		//setter
		//$( "#check1" ).button( "option", "icons", {primary:'ui-icon-check',secondary:'ui-icon-triangle-1-s'} );
        //$("#user_id2_property_set").removeClass("hidden");
	} 
	$(function() {
        DealDialogPropertyRefresh();
	});
	</script>


<!--
<div id="dialog_loading" class="dialog" title="Loading">
	<p>Loading</p>
	<div id="progressbar"></div>
</div>
-->
<div id="dialogbox">
<div id="dialog_auction_start" class="dialog" title="Create auction">
	    <p class="validateTips"></p>
	<form>
	<fieldset>
		<div id="auction_form_box" >
		  <label for="bid">Bid</label>
		  <input type="text" name="bid" value="" id="bid" class="text ui-widget-content ui-corner-all" />
	    </div>
	</fieldset>
	</form>
</div>

<div id="dialog_deal_start" class="dialog" title="Create offer">
    <p class="validateTips"></p>
    <form>
	<fieldset>
<div class="ui-widget">
	<label>Select user: </label>
	<select id="opponentcombobox" >
		<option value="">Select one...</option>
		<?php
			$list_tpl ='<option value="%USER_ID%">%NAME%</option>';
			echo $gsession -> GetUserOponentList($current_user_id, $list_tpl);
		?>
	</select>
</div>
<div id="select_deal_box" style ="width:100%">
<div id="owner_deal_box" class="right" style ="width:45%;padding: 5px;">
		<label for="ownercash">Cash</label>
		<input type="text" name="ownercash" value="" id="ownercash" class="text ui-widget-content ui-corner-all" />
<div id="property_set_owner">
<?php 
//	$proplist_tpl='<input type="checkbox" id="chk_user_id%USER_ID%_field_id%FIELD_ID%" /><label for="chk_user_id%USER_ID%_field_id%FIELD_ID%">%FIELD_NAME%</label></br>';
//	echo $gsession -> GetUserPropertyList($current_user_id, $proplist_tpl);
?>
</div>
</div>
<div id="opponent_deal_box" class="left" style ="width:45%;padding: 5px;">
		<label for="opponentcash">Cash</label>
		<input type="text" name="opponentcash" value=""  id="opponentcash" class="text ui-widget-content ui-corner-all" />
<?php 
	$proplist_tpl='<div id="property_set_user_id%USER_ID%" class="opponent_property_sets right hidden"></div>';
	echo $gsession -> GetUserOponentList($current_user_id, $proplist_tpl);
?>
</div>
</div>
<div style ="clear: both;">
</div>
	</fieldset>
	</form>
</div>
</div>

<?php } ?>
<div id="iboard">
	<div style="clear: both;">
		<?php
		for ($i = 1; $i <= 10; $i++) {
			//SELECT cf.field_id, f.fparam, f.owner_user_id, cf.fcode, cf.name, cf.fact_code, cf.ftype_code, cf.fgroup_id

			//function GetFieldInfo_by_fcode($fcode,$tpl,$encodechars=false, $rowdelimter='') {
			$tpl = str_replace('%color%', GetNextColor(), str_replace('%i%', $i, $ceil_tmpl_top));
			//$tpl = $gsession -> GetFieldInfo_by_fcode($i, $tpl);
			//$tpl_ulist = $gsession -> GetFieldUserInfo_by_fcode($i, $tpl_ceil_user);
			//echo str_replace('%USERLIST%', $tpl_ulist, $tpl);
            echo $tpl;
		}
		?>
	</div>
	<div style="width:5em;float: left;">
		<?php
		GetNextColor();
		for ($i = 32; $i >= 27; $i--) {
			$tpl = str_replace('%color%', GetNextColor(), str_replace('%i%', $i, $ceil_tmpl_left));
			//$tpl = $gsession -> GetFieldInfo_by_fcode($i, $tpl);
			//$tpl_ulist = $gsession -> GetFieldUserInfo_by_fcode($i, $tpl_ceil_user);
			//echo str_replace('%USERLIST%', $tpl_ulist, $tpl);
            echo $tpl;
		}
		?>
	</div>
	<div style="float: left;">
		<section id="con" class="player1 teal right">
			<div id="infobar" style="width: 100%;height:30%">
				<div id="userinfobox" class="left" style="width: 50%;height:100%">
				<div id="userinfo" style="width: 100%;height:10%">
					User:<b><?php echo $current_user_name; ?></b>
					
				</div>
				<div id="usermonopoly" style="width: 100%;height:90%">
					
				</div>
				</div>
				<div id="dicebox" class="left" style="width: 20%;height:100%">
				<div id="bot" class="left"></div>	
				<div id="dice" style="width: 100%;height:60%" onclick="DoAction1()" >
					<div id="dicer"><img src="images/dice.gif" /></div>					
				</div>
				<div id="actbtnbox" style="width: 100%;height:40%" >
				<div id="lastupdated"></div>
				<!--<button class="button" id="btn_start_deal" onclick="DoStartDeal()">Deal</button>-->
				<?php if ($G_MODE == G_MODE_PLAY) { ?>
				<button id="create-deal">Deal</button>
				<?php } ?>
				</div>
				</div>
				<div id="userlist" class="left" style="width: 30%;height:100%" onclick="SwitchBot()">
					List of users:</br>
					<table>
						<?php
						$list_tpl = "
						<tr>
							<td><div id=uc%ACT_ORDER% class='pl us_c%ACT_ORDER%'></td><td class='us_c%IS_HOLDER%'><b>%NAME%</b></td><td>
				</div> %USER_CASH%</td></tr>";
						echo $gsession -> GetUserList($list_tpl);
				?>
				</table>
			    </div>
	       </div>
	<div id=scrollbar style="clear: both; width: 100%;height:70%">
		<div id=consolebox  class="consolebox left" style="width: 50%; height:100%" >
		<div id=gmsgbox class="gmsgbox" style="width: 100%; height:50%;" >
			<?php //echo "Welcome " . $current_user_name; ?>
		
		</div>
		<div id=actlog  class="actlog" style="width: 100%; height:25%;" >
		</div>
		<div id=console  class="console" style="width: 100%; height:25%;" >
		</div>		
		</div>
		<div id=auctbox  class="right" style="width: 50%;height:100%">
			<div id=auctions >
			
            </div>
		</div>
	</div>
	</section>
</div>
<div style="width:5em;float: left;">
	<?php
	GetNextColor();
	for ($i = 11; $i <= 16; $i++) {
		//    		echo str_replace('%color%',GetNextColor(),str_replace('%i%',$i,$ceil_tmpl));
		$tpl = str_replace('%color%', GetNextColor(), str_replace('%i%', $i, $ceil_tmpl_right));
		//$tpl = $gsession -> GetFieldInfo_by_fcode($i, $tpl);
		//$tpl_ulist = $gsession -> GetFieldUserInfo_by_fcode($i, $tpl_ceil_user);
		//echo str_replace('%USERLIST%', $tpl_ulist, $tpl);
        echo $tpl;

	}
	?>
</div>
<div style="clear: both;">
	<?php

	GetNextColor();
	for ($i = 26; $i >= 17; $i--) {
		$tpl = str_replace('%color%', GetNextColor(), str_replace('%i%', $i, $ceil_tmpl_bottom));
		//$tpl = $gsession -> GetFieldInfo_by_fcode($i, $tpl);
		//$tpl_ulist = $gsession -> GetFieldUserInfo_by_fcode($i, $tpl_ceil_user);
		//echo str_replace('%USERLIST%', $tpl_ulist, $tpl);
        echo $tpl;
	}
	?>
</div>
</div>

