<div class='input checkbox %%name%%'>
		{if:before_input} %%before_input%% {/if:before_input}
		<input type='checkbox' name='%%name%%' id='%%id%%' data-field='%%name%%'
			{if:inputclass}class="%%inputclass%%"{/if:inputclass}
			value='%%value%%'
			%%checked%%
			{if:icon} tabindex='-1'{/if:icon}
			{if:disabled} disabled {/if:disabled}
		/>
		<label for='%%id%%' {if:title}title="%%title%%"{/if:title} >
		{if:after_input} %%after_input%% {/if:after_input}
		{if:icon}	<i class='dashicons %%icon%%' tabindex='0'></i>	{/if:icon}
		{if:label}	%%label%% {/if:label}

		</label>
</div>
