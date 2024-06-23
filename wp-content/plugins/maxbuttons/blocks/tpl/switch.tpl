	  {if:label}	<label for='%%id%%' class='switch_label %%name%%'>%%label%%</label> {/if:label}
<div class='input switch_button %%name%%'>
		<label for='%%id%%' tabindex='0'>
		<input type='checkbox' name='%%name%%' id='%%id%%' data-field='%%name%%'
			value='%%value%%'
			%%checked%%
			tabindex='-1'
			{if:disabled} disabled {/if:disabled}
		/>

		<div class='the_switch %%input_class%%' >

		{if:icon}	<i class='dashicons %%icon%%'></i>	{/if:icon}
		</div>
		</label>
		{if:label_after}	<label for='%%id%%' class='switch_label %%name%% %%input_class%%'>%%label_after%%</label> {/if:label_after}
		{if:error}<p class='error'>%%error%%</p>{/if:error}
		{if:warning}<p class='warning'>%%warning%%</p>{/if:warning}
		{if:help}<div class="help dashicons dashicons-info "><span>%%help%%</span></div>{/if:help}
</div>
