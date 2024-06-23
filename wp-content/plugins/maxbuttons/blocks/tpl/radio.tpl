
<div class='input radio %%name%%'>
		{if:before_input} %%before_input%% {/if:before_input}
		<input type='radio' name='%%name%%' id='%%id%%' data-field='%%name%%'
			{if:inputclass}class="%%inputclass%%"{/if:inputclass}
			value='%%value%%'
			%%checked%%
			{if:icon}tabindex='-1'{/if:icon}
			{if:disabled} disabled {/if:disabled}
		/>
		{if:after_input} %%after_input%% {/if:after_input}
		<label for='%%id%%' {if:title}title="%%title%%"{/if:title}>
		{if:icon}	<i class='dashicons %%icon%%' tabindex='0'></i>	{/if:icon}
 	  {if:custom_icon} <i class='%%custom_icon%%'></i>{/if:custom_icon}
		{if:image} <img src='%%image%%' /> {/if:image}
		{if:label} %%label%% {/if:label}
		</label>
		{if:help}<div class="help dashicons dashicons-info "><span>%%help%%</span></div>{/if:help}

</div>
