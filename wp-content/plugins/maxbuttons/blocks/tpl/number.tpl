		{if:label} 	<label for='%%id%%'>%%label%%</label> {/if:label}
		<div class="input number %%name%%" {if:conditional}data-show="%%conditional%%"{/if:conditional} >
		{if:before_input} %%before_input%% {/if:before_input}
			<input type="number"
				id="%%id%%"
				name="%%name%%"
				value="%%value%%"
 				{if:min} min="%%min%%" {/if:min}
 				{if:max} max="%%max%%" {/if:max}
				placeholder="%%placeholder%%"
				{if:disabled} disabled {/if:disabled}
				{if:inputclass}class="%%inputclass%%"{/if:inputclass}
			/>
			{if:after_input} %%after_input%% {/if:after_input}
		{if:help}<div class="help dashicons dashicons-info "><span>%%help%%</span></div>{/if:help}
		{if:error}<p class='error'>%%error%%</p>{/if:error}
		{if:warning}<p class='warning'>%%warning%%</p>{/if:warning}
		</div>
		{if:default} <div class='default'>%%default%%</div> {/if:default}
