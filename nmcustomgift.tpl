<!-- Block nmcustomgift -->
<div id='nmCustomGiftPopup' style='position: fixed; top:0; left:0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);z-index:10000;'>
	<div style="top: 100px; display: block; box-shadow:10px 10px 10px #000;" id="layer_cart">
		<div class="clearfix">
			<div class="layer_cart_product col-xs-12 col-md-6" id='nmCustomGiftPopup2'  >
				<span class="cross" title="Close window" onclick="document.getElementById('nmCustomGiftPopup').style.display='none'; document.getElementById('nmCustomGiftPopup2').style.display='none';"></span>
				<h2 style='font-family: "LMRomanCaps10-Regular","Times New Roman",Helvetica;' > <i class="icon-ok"></i>{$nmcustomgift_title}</h2>
				
					<table style="width:620px; height: 300px;">
						<tr>
							<td>
								<img src='{$nmcustomgift_image_url}' height=300 />
							</td>
							<td>
								<p style='font-size:1.5em; margin: 0 0 5px 0; '>{$nmcustomgift_info}</p>
								<textarea id='customGiftMessage' style='width:300px;height:200px;font-family: "LMRomanCaps10-Regular","Times New Roman",Helvetica;font-size:1.5em;'></textarea><br />
								<button onclick="document.location='?saveCustomGift='+encodeURIComponent( document.getElementById('customGiftMessage').value.replace(/\n/g, ' / ', 'g') );" style="font-size:1.5em;">{$nmcustomgift_save}</button><br />
								<p>* {$nmcustomgift_info2}</p>
								
							</td>
						</tr>
					</table>
				
			</div>
		</div>
	</div>
</div>

<!-- /Block nmcustomgift -->   
