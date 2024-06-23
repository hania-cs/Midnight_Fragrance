	{if:label}	<label for='%%id%%'>%%label%%</label> {/if:label}
		<div class="input">
			{if:before_input} %%before_input%% {/if:before_input}
			<textarea class='large-text' rows='3' id="%%id%%" placeholder="%%placeholder%%" name="%%name%%"  >%%value%%</textarea>
			{if:after_input} %%after_input%% {/if:after_input}
			{if:help}<div class="help dashicons dashicons-info "><span>%%help%%</span></div>{/if:help}
			{if:error}<p class='error'>%%error%%</p>{/if:error}
			{if:warning}<p class='warning'>%%warning%%</p>{/if:warning}
		</div>
