(function() {
        // Load plugin specific language pack
        //tinymce.PluginManager.requireLangPack('example');

        tinymce.create('tinymce.plugins.BPCheckinsImg', {
                /**
                 * Initializes the plugin, this will be executed after the plugin has been created.
                 * This call is done before the editor instance has finished it's initialization so use the onInit event
                 * of the editor instance to intercept that event.
                 *
                 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
                 * @param {string} url Absolute URL to where the plugin is located.
                 */
                init : function(ed, url) {
                        // Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
                        ed.addCommand('mceCheckinsImg', function() {
	
							ed.windowManager.open({
							                                    file : url + '/box.php',
							                                    width : 500,
							                                    height : 300,
							                                    inline : 1,
							                            }, {
							                                    plugin_url : url, // Plugin absolute URL
							                                    editorContent : 'ed.selection.getContent()' // Custom argument
							                            });
                            });
                                /*img_post = prompt("Url to your image", "http://");
								if(img_post){
									img_width = prompt("Width in pixels of your image (only numbers)");
									max_width_img = 300;
									if(parseInt(img_width) < max_width_img) max_width_img = parseInt(img_width);
									ed.selection.setContent('<img src="'+img_post+'" width="'+max_width_img+'px">');
								}
                                });*/
                        //});
						//alert(url);
						
                        // Register example button
                        ed.addButton('BPCheckinsImg', {
                                title : 'Add a photo',
                                cmd : 'mceCheckinsImg',
                                image : url + '/img/bpci-image.gif',
								/*onclick : function() {
					                // Add you own code to execute something on click
									
					            }*/
                        });

                        // Add a node change handler, selects the button in the UI when a image is selected
                        ed.onNodeChange.add(function(ed, cm, n) {
                                cm.setActive('BPCheckinsImg', n.nodeName == 'IMG');
                        });
                },

                /**
                 * Creates control instances based in the incomming name. This method is normally not
                 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
                 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
                 * method can be used to create those.
                 *
                 * @param {String} n Name of the control to create.
                 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
                 * @return {tinymce.ui.Control} New control instance or null if no control was created.
                 */
                createControl : function(n, cm) {
                        return null;
                },

                /**
                 * Returns information about the plugin as a name/value array.
                 * The current keys are longname, author, authorurl, infourl and version.
                 *
                 * @return {Object} Name/value array containing information about the plugin.
                 */
                getInfo : function() {
                        return {
                                longname : 'BP Checkins Photo menu',
                                author : 'imath',
                                authorurl : 'http://imath.owni.fr',
                                infourl : 'http://imath.owni.fr',
                                version : "1.0"
                        };
                }
        });

        // Register plugin
        tinymce.PluginManager.add('BPCheckinsImg', tinymce.plugins.BPCheckinsImg);
})();