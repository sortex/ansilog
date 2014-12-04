<div style="border-top: 10px double #E4EAF9; border-bottom: 10px double #EDEFF4" xmlns="http://www.w3.org/1999/html">
	<div style="border-top: 10px double #E4EAF9">
		<h3 style="padding: 10px 0 15px; margin: 0; text-align: center; background-color: #E4EAF9">
			<?php echo "$site $version $environment" ?>
		</h3>
		<div style="padding: 10px 0 15px; background-color: #E4EAF9; color: #545454; font-weight: bold; font-family: Verdana, arial, sans-serif">
			<div style="width: 80%; margin: 0 auto; text-align: left;">
				<p style="font-size: 11px; margin-top: 0">Technical error:</p>
				<ul>
					<li>
						<p style="font-size: 13px; margin: 4px 0">Page => <?php echo HTML::anchor($url, $url, array('target' => '_blank', 'style' => 'color: #000')) ?></p>
					</li>
					<li>
						<p style="font-size: 13px; margin: 4px 0">File => <span style="color: #000"><?php echo $exception->getFile() ?></span></p>
					</li>
					<li>
						<p style="font-size: 13px; margin: 4px 0">Line => <span style="color: #000"><?php echo $exception->getLine() ?></span></p>
					</li>
					<li>
						<p style="font-size: 13px; margin: 4px 0">Error => <span style="color: #920b1e"><?php echo $exception->getMessage() ?></span></p>
					</li>
				</ul>

				<p style="font-size: 11px; margin: 4px 0">From: </p>

				<ul>
					<?php if (isset($user) && is_object($user) && $user->id): ?>
						<?php if (isset($site)): ?>
							<li>
								<p style="font-size: 13px; margin: 4px 0">Site => <span style="color: #000"><?php echo $site ?></span></p>
							</li>
						<?php endif ?>
						<li>
							<p style="font-size: 13px; margin: 4px 0">Account => <span style="color: #000">[#<?php echo $account ?>]</span></p>
						</li>
						<li>
							<p style="font-size: 13px; margin: 4px 0">User => <span style="color: #000"><?php echo $username ?> [#<?php echo $user->id ?>]</span></p>
						</li>
					<?php else: ?>
						<li>
							<p style="font-size: 13px; margin: 4px 0">Guest (Not logged in)</p>
						</li>
					<?php endif ?>
					<li>
						<p style="font-size: 13px; margin: 4px 0">IP Address => <a href="http://whatismyipaddress.com/ip/<?php echo $ip ?>" target="_blank" style="color: #000"><?php echo $ip ?></a></p>
					</li>
				</ul>

				<p style="font-size: 11px; margin: 4px 0">On <?php echo date('d/m/Y H:i:s') ?></p>
			</div>
		</div>
	</div>
</div>
<h3 style="margin-bottom: 0; padding: 0; line-height: 60%; font-style: italic">Backtrace</h3>
<div style="padding: 0 0 0 10px; background-color:#e3e3e3; border-top: 1px solid #ccc;
		border-bottom: 1px solid #ddd; font-size: 11px; font-family: Courier New, Verdana, sans-serif">
	<?php echo $exception->getTraceAsString() ?>
</div>
<h3 style="margin-bottom: 0; padding: 0; line-height: 60%; font-style: italic">$_GET</h3>
<div style="padding: 0 0 0 10px; background-color:#e3e3e3; border-top: 1px solid #ccc;
		border-bottom: 1px solid #ddd; font-size: 11px; font-family: Courier New, Verdana, sans-serif">
	<?php echo $get ?>
</div>
<h3 style="margin-bottom: 0; padding: 0; line-height: 60%; font-style: italic">$_POST</h3>
<div style="padding: 0 0 0 10px; background-color:#e3e3e3; border-top: 1px solid #ccc;
		border-bottom: 1px solid #ddd; font-size: 11px; font-family: Courier New, Verdana, sans-serif">
	<?php echo $post ?>
</div>
<h3 style="margin-bottom: 0; padding: 0; line-height: 60%; font-style: italic">$_SERVER</h3>
	<div style="padding: 0 0 0 10px; background-color:#e3e3e3; border-top: 1px solid #ccc;
		border-bottom: 1px solid #ddd; font-size: 11px; font-family: Courier New, Verdana, sans-serif">
	<?php echo $server ?>
</div>
