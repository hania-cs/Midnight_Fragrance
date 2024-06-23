{if:label} 	<label for='%%id%%' class='color'>%%label%%</label> {/if:label}
{if:before_input} %%before_input%% {/if:before_input}
<div class="input option_select %%name%%"  {if:conditional}data-show="%%conditional%%"{/if:conditional}>
	<select name='%%name%%' id='%%id%%'
		{if:inputclass}class="%%inputclass%%"{/if:inputclass}
		{if:disabled} disabled {/if:disabled}
	>
		{for:options}
			<option value='%%key%%' {if:selected=%%key%%} selected {/if:selected=%%key%%} >%%item%%</option>
		{/for:options}
	</select>
	{if:help}<div class="help dashicons dashicons-info "><span>%%help%%</span></div>{/if:help}
	{if:error}<p class='error'>%%error%%</p>{/if:error}
	{if:warning}<p class='warning'>%%warning%%</p>{/if:warning}
</div>
