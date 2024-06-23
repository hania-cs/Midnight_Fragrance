		{if:label} 	<label for='%%id%%' class='mbcolor'>%%label%%</label> {/if:label}

	<div class="input mbcolor alpha-color-picker %%name%%" {if:conditional}data-show="%%conditional%%"{/if:conditional}>

		<input type="text" name="%%name%%" id="%%id%%" class="mb-color-field" value="%%value%%" data-alpha-enabled="true"  data-alpha-color-type="hex">
		{if:copycolor} <div class="arrows %%copypos%%" data-id="%%id%%" data-bind="%%bindto%%"><div class='right'><span class='arrow-right' title='%%right_title%%'></span></div><div class='left'><span class='arrow-left' title='%%left_title%%'></span></div></div>	{/if:copycolor}
	</div>
