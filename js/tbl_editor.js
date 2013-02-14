(function($) {
	
$.widget("ui.tblEditor", {
	options: { // initial values are stored in the widget's prototype
		tbl: null,
		ths: [],
		omit: [],
		allow_delete: true,
		allow_edit: true,
		allow_add: true,
		ddl_cols: [],
		date_cols: [],
		ajax_url: "ajax.php",
		onSave: function(data) {
			return data;
		},
		onDelete: function(data) {
			return data;
		},
		onAdd: function(data) {
			return data;
		},
		confirm_dialog: $("<div title='Confirmation Required'>Are you sure you want to delete this row?</div>")
	},
	getHeaderData: function() { return this.options.ths; },
	writeMessage: function(msg,is_success) {
		if (is_success === undefined)
			$(this.options.msg_div).html($("<span style='color: #000')'>" + msg + "</span>")); 
		else if (this.getStreet() != "")
			$(this.options.msg_div).html($("<span style='color: " + (is_success ? "#0C0" : "#F00") + "'>" + msg + "</span>")); 
	},
	_create: function() {
		var base = this;
		this.options.tbl = this.element;
		// create the confirm dialog box
	    $(this.options.confirm_dialog).dialog({
            autoOpen: false,
            modal: true,
	        buttons : {
	          "Confirm" : function() {
	        	  return true;
	          },
	          "Cancel" : function() {
	            $(this).dialog("close");
	            return false;
	          }
	        }
	    });
		// grab all the table headers
		// append blank column to header row to serve as place holder for edit/save buttons
		var trs = $(this.element).find("thead tr");
		for (var x=0; x<trs.length; x++) {
			var ths = $(trs[x]).find("th");
			for (var y=0; y<ths.length; y++) {
				th = { };
				var txt = $(ths[y]).text();
				txt = txt.toLowerCase();
				txt = txt.replace(/\s/g, "_");
				th.name = txt;
				th.type = "text";
				if ($(ths[y]).attr("data-ddl")) {
					var ddl_data = $(ths[y]).data("ddl"); 
					//alert(ddl_data);
					th.type = "ddl";
					th.ddl = ddl_data;
				}
				else if ($(ths[y]).attr("data-type")) {
					th.type = $(ths[y]).attr("data-type");
				}
				this.options.ths.push(th);
			}
			$(trs[x]).append($("<th data-type='none'>&nbsp;</th>"));
		}
		
		// add the two button divs to every tr in tbody
		var trs = $(this.element).find("tbody tr");
		for (var x=0; x<trs.length; x++) { 
			$(trs[x]).append(this.genEditButtons(true));
		}
		
		// add add button
		if (this.options.allow_add) {
			// check for tfoot
			var tfoot = $(this.element).find("tfoot");
			if (tfoot.length == 0) {
				tfoot = $("<tfoot></tfoot>");
				$(this.element).append(tfoot);
			}
			var tr = $("<tr></tr>");
			var td = $("<td colspan='" + (this.options.ths.length+1) + "'></td>");
			var btn_add = $("<a href='#'>add new</a>");
			$(btn_add).button({
				icons:  { primary: "ui-icon-plusthick" },
			});
			$(btn_add).bind('click', {context:this}, this.add );
			
			$(td).append(btn_add);
			$(tr).append(td);
			$(tfoot).append(tr);
		}
		var msg_div = $("<div style='position: relative; height: 1em'><div class='msg_div' style='position: absolute; top: 0; left: 0;'></div></div>");

	},
	_init: function() {
	},
	genEditButtons: function(show_edit) {
		var td = $("<td style='padding-right: 0px;'></td>");
		var disp_edit = show_edit ? "" : "style='display: none'";
		var disp_save = show_edit ? "style='display: none'" : "";
		var div_edit_del = $("<div " + disp_edit + " class='div_edit_del'></div>");
		var btn_edit = $("<a href='#'>edit</a>");
		var btn_del = $("<a href='#'>delete</a>");
		var div_save_cancel = $("<div " + disp_save + " class='div_save_cancel'></div>");

		if (this.options.allow_edit) {
			var btn_save = $("<a href='#'>save</a>");
			var btn_cancel = $("<a href='#'>cancel</a>");
			$(btn_del).button({
				icons:  { primary: "ui-icon-closethick" },
				text: false
			});
			$(btn_edit).button({
				icons:  { primary: "ui-icon-pencil" },
				text: false
			});
			$(btn_save).button({
				icons:  { primary: "ui-icon-disk" },
				text: false
			});
			$(btn_cancel).button({
				icons:  { primary: "ui-icon-arrowreturnthick-1-w" },
				text: false
			});
			
			$(div_edit_del).append(btn_edit);
			$(div_save_cancel).append(btn_save);
			$(div_save_cancel).append(btn_cancel);
			
			$(btn_edit).bind('click', {context:this}, this.edit );
			$(btn_cancel).bind('click', {context:this}, this.cancel );
			$(btn_save).bind('click', {context:this}, this.save );
		}
		if (this.options.allow_delete) {
			$(div_edit_del).append(btn_del);
			$(btn_del).bind('click', {context:this}, this.del );
		}
		
		$(td).append(div_edit_del);
		$(td).append(div_save_cancel);
		return td;
		
	},
	edit: function(event) {
		var ctx = event.data.context;
		var tr = $(this).parents("tr");
		$(this).parent().hide();
		$(tr).find(".div_save_cancel").show();
		// go through all the elements in the row and make them editable
		var tds = $(tr).find("td");
		var ths = ctx.options.ths;
		for (var x=0; x<tds.length-1; x++) {
			if (ths[x].type != "none") {
				var txt = $(tds[x]).text();
				$(tds[x]).attr("data-orig", txt);
				var inp = $("<input type='text' name='" + ths[x].name + "' value='" + txt + "'/>");
				if (ths[x].type == "date") {
					$(inp).datepicker();
				}
				else if (ths[x].type == "ddl") {
					var options = "";
					var ddl = ths[x].ddl;
					for (var y=0; y<ddl.length; y++) {
						var selected = (txt == ddl[y][0] || txt == ddl[y][1]) ? "selected='selected'" : "";
						options += "<option value='" + ddl[y][0] + "' " + selected + ">" + ddl[y][1] + "</option>"; 
					}
					inp = $("<select name='" + ths[x].name + "'>" + options + "</select>");
				}
				$(tds[x]).html(inp);
			}
		}
		return false;
	},
	cancel: function(event) {
		var ctx = event.data.context;
		var tr = $(this).parents("tr");
		if ($(tr).hasClass("new")) {
			$(tr).fadeOut(function() { $(this).remove(); });
		}
		else {
			$(this).parent().hide();
			$(tr).find(".div_edit_del").show();
			// go through all the elements in the row and make them editable
			var tds = $(tr).find("td");
			var ths = ctx.options.ths;
			for (var x=0; x<tds.length-1; x++) {
				if (ths[x].type != "none") {
					$(tds[x]).html($(tds[x]).attr("data-orig"));
				}
			}
		}
		return false;
	},
	save: function(event) {

		var ctx = event.data.context;
		var tr = $(this).parents("tr");
		var is_insert = $(tr).hasClass("new");
		var q = is_insert ? "insert" : "save";
		var tds = $(tr).find("td");
		var ths = ctx.options.ths;
		var getdata = { q: q, tbl: $(ctx.options.tbl).attr("id") };
		if (!is_insert)
			getdata.id = $(tr).attr("id");
		for (var x=0; x<tds.length-1; x++) {
			if (ths[x].type != "none") {
				var inp = $(tds[x]).find(":input");
				//alert("name:" + $(inp).attr("name") + "val:" + $(inp).val());
				getdata[$(inp).attr("name")] = $(inp).val();
			}
		}
		$.ajax({
			  type: "POST",
			  url: ctx.options.ajax_url,
			  data: getdata,
			  dataType: "json",
			  tr: tr,
			  ctx: ctx,
			  error: function(xhr, status, erro) {
				  alert(xhr.responseText);
			  }
		}).done(function(data) {
			var callback = this.ctx.options.onSave;
			var msg = callback(data);
			if (msg.success) {
				var ths = ctx.options.ths;
				var tds = $(this.tr).find("td");
				if ($(this.tr).hasClass("new")) {
					$(this.tr).removeClass("new");
					$(this.tr).removeClass("ui-state-highlight");
					$(this.tr).attr("id",msg.id);
				}
				for (var x=0; x<tds.length-1; x++) {
					if (ths[x].type != "none") {
						var inp = $(tds[x]).find(":input");
						$(tds[x]).attr("data-orig", $(inp).val());
						$(tds[x]).html($(inp).val());
					}
				}
				$(tds[tds.length-1]).find(".div_save_cancel").hide();
				$(tds[tds.length-1]).find(".div_edit_del").show();
			}
			else {
				alert(JSON.stringify(data));
				// display error message
			}
		});
		return false;
	},
	del: function(event) {
		if (confirm("Are you sure you want to delete this row?")) {
		var ctx = event.data.context;
		var tr = $(this).parents("tr");
		var getdata = { q: "delete", tbl: $(ctx.options.tbl).attr("id"), id: $(tr).attr("id") };
		$.ajax({
			  type: "POST",
			  url: ctx.options.ajax_url,
			  data: getdata,
			  dataType: "json",
			  tr: tr,
			  ctx: ctx,
			  error: function(xhr, status, error) {
				  alert(xhr.responseText);
			  }
		}).done(function(data) {
			var callback = this.ctx.options.onDelete;
			var msg = callback(data);
			if (msg.success) {
				// remove the row
				$(this.tr).fadeOut(function() { $(this).remove(); });
			}
			else {
				// display error message
			}
		});
		}
		return false;
	},
	add: function(event) {
		var ctx = event.data.context;
		var ths = ctx.options.ths;
		var tr = $("<tr class='new ui-state-highlight'></tr>");
		var tbody = $(ctx.options.tbl).find("tbody");
		for (var x=0; x<ths.length; x++) {
			var td = $("<td></td>");
			var txt = "";
			$(td).attr("data-orig", txt);
			var inp = $("<input type='text' name='" + ths[x].name + "' value='" + txt + "'/>");
			if (ths[x].type == "none") {
				inp = " ";
			}
			else if (ths[x].type == "date") {
				$(inp).datepicker();
			}
			else if (ths[x].type == "ddl") {
				var options = "";
				var ddl = ths[x].ddl;
				for (var y=0; y<ddl.length; y++) {
					var selected = (txt == ddl[y][0] || txt == ddl[y][1]) ? "selected='selected'" : "";
					options += "<option value='" + ddl[y][0] + "' " + selected + ">" + ddl[y][1] + "</option>"; 
				}
				inp = $("<select name='" + ths[x].name + "'>" + options + "</select>");
			}
			$(td).html(inp);
			$(tr).append(td);

		}
		$(tr).append(ctx.genEditButtons(false));
		$(tbody).append(tr);
		return false;
		
	},
	off: function() {
		this.element.html('');
		this.destroy(); // use the predefined function
	}
});

})(jQuery);

