<?php

final class ITSEC_Two_Factor_Settings_Page extends ITSEC_Module_Settings_Page {
	private $version = 4;


	public function __construct() {
		$this->id = 'two-factor';
		$this->title = __( 'Two-Factor Authentication', 'it-l10n-ithemes-security-pro' );
		$this->description = __( 'Two-Factor Authentication greatly increases the security of your WordPress user account by requiring additional information beyond your username and password in order to log in.', 'it-l10n-ithemes-security-pro' );
		$this->type = 'recommended';
		$this->pro = true;

		parent::__construct();
	}

	public function enqueue_scripts_and_styles() {
		wp_enqueue_script( 'itsec-two-factor-script', plugins_url( 'js/settings-page.js', __FILE__ ), array( 'jquery' ), $this->version, true );

		wp_enqueue_style( 'itsec-two-factor-style', plugins_url( 'css/settings-page.css', __FILE__ ), array(), $this->version );
	}

	protected function render_description( $form ) {

?>
	<p><?php printf( wp_kses( __( 'Two-Factor Authentication greatly increases the strength of a user account by requiring a secondary code in addition to a username and password when logging in. Once Two-Factor Authentication is enabled here, users can visit their <a href="%s">profile</a> to enable two-factor for their account. The following settings allow you to enforce the use of two-factor on accounts based on different criteria.', 'it-l10n-ithemes-security-pro' ), array( 'a' => array( 'href' => array() ) ) ), esc_url( admin_url( 'profile.php' ) ) ); ?></p>
<?php

	}

	/** @param ITSEC_Form $form */
	protected function render_settings( $form ) {
		/** @var ITSEC_Two_Factor_Validator $validator */
		$validator = ITSEC_Modules::get_validator( $this->id );

		$available_methods = $validator->get_available_methods();
		$methods = $validator->get_methods();
?>
	<table class="form-table" id="two-factor-methods">
		<tr>
			<th scope="row"><label for="itsec-two-factor-available_methods"><?php esc_html_e( 'Authentication Methods Available to Users', 'it-l10n-ithemes-security-pro' ); ?></label></th>
			<td>
				<?php $form->add_select( 'available_methods', $available_methods ); ?>
				<p class="description"><?php esc_html_e( 'iThemes Security supports multiple two-factor methods: mobile app, email, and backup codes. Selecting "All Methods" is highly recommended so that users can use the method that works the best for them.', 'it-l10n-ithemes-security-pro' ); ?></p>
			</td>
		</tr>
		<tr id="itsec-two-factor-available_methods-container">
			<th scope="row"><?php esc_html_e( 'Select Available Methods', 'it-l10n-ithemes-security-pro' ); ?></th>
			<td>
				<?php foreach ( $methods as $class => $provider ) : ?>
					<?php $form->add_multi_checkbox( 'custom_available_methods', get_class( $provider ) ); ?>
					<label for="itsec-two-factor-custom_available_methods-<?php echo esc_attr( get_class( $provider ) ); ?>"><?php $provider->print_label(); ?></label>
					<?php do_action( 'two-factor-admin-options-' . $class ); ?>
					<br />
				<?php endforeach; ?>
			</td>
		</tr>
		<tr class="itsec-two-factor-requires-no-email-provider">
			<td colspan="2">
				<div class="itsec-notice-message">
					<p><?php printf( wp_kses( __( '<strong>Notice:</strong> The following Two-Factor Authentication features require the email method in order to function:', 'it-l10n-ithemes-security-pro' ), array( 'strong' => array() ) ) ); ?></p>
					<ul>
						<li><?php esc_html_e( 'User Type Protection', 'it-l10n-ithemes-security-pro' ); ?></li>
						<li><?php esc_html_e( 'Vulnerable User Protection', 'it-l10n-ithemes-security-pro' ); ?></li>
						<li><?php esc_html_e( 'Vulnerable Site Protection', 'it-l10n-ithemes-security-pro' ); ?></li>
					</ul>
					<p><?php esc_html_e( 'Since the email method is disabled, these features are not available.', 'it-l10n-ithemes-security-pro' ); ?></p>
				</div>
			</td>
		</tr>
		<tr class="itsec-two-factor-requires-email-provider">
			<th scope="row"><label for="itsec-two-factor-protect_user_type"><?php esc_html_e( 'Force Two Factor', 'it-l10n-ithemes-security-pro' ); ?></label></th>
			<td>
				<?php $form->add_user_groups( 'protect_user_group', $this->id ); ?>
				<p class="description"><?php esc_html_e( 'Require users in a group to use Two-Factor authentication. We highly recommended forcing any user that can make changes to the site to use two-factor authentication.', 'it-l10n-ithemes-security-pro' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="itsec-two-factor-exclude_type"><?php esc_html_e( 'Disable Two-Factor Onboarding', 'it-l10n-ithemes-security-pro' ); ?></label></th>
			<td>
				<?php $form->add_user_groups( 'exclude_group', $this->id ); ?>
				<p class="description">
					<?php esc_html_e( 'Disable the two-factor authentication on-boarding for certain users. Users can still manually enroll in two-factor through their WordPress admin profile. This setting will override forced two-factor authentication for Vulnerable User Protection and Vulnerable Site Protection for the selected users..', 'it-l10n-ithemes-security-pro' ); ?>
				</p>
				<p class="description">
					<?php esc_html_e( 'Note: We don???t recommend excluding users from onboarding, as two-factor authentication is important for all users, not just administrators.', 'it-l10n-ithemes-security-pro' ); ?>
				</p>
			</td>
		</tr>
		<?php if ( ITSEC_Modules::is_active( 'fingerprinting' ) ) : ?>
			<tr>
				<th scope="row"><label for="itsec-two-factor-allow_remember"><?php esc_html_e( 'Allow Remembering Device', 'it-l10n-ithemes-security-pro' ); ?></label></th>
				<td>
					<?php $form->add_user_groups( 'remember_group', $this->id ); ?><br>
					<p class="description"><?php esc_html_e( 'Allow users to check a "Remember this Device" box that, if checked, will not prompt the user for a Two-Factor code for the next 30 days on the current device. Requires the Trusted Devices feature.', 'it-l10n-ithemes-security-pro' ); ?></p>
					<p class="description"><?php esc_html_e( 'Note: While remembering devices is convenient, it is more secure to require users to generate a new Two-Factor token every login.', 'it-l10n-ithemes-security-pro' ) ?></p>
				</td>
			</tr>
		<?php endif; ?>
		<tr class="itsec-two-factor-requires-email-provider">
			<th scope="row"><label for="itsec-two-factor-protect_vulnerable_users"><?php esc_html_e( 'Vulnerable User Protection', 'it-l10n-ithemes-security-pro' ); ?></label></th>
			<td>
				<?php $form->add_checkbox( 'protect_vulnerable_users' ); ?>
				<label for="itsec-two-factor-protect_vulnerable_users"><?php esc_html_e( 'Enforce two-factor for vulnerable users.', 'it-l10n-ithemes-security-pro' ); ?></label>
				<p class="description"><?php esc_html_e( "Require user accounts that are considered vulnerable, such as having a weak password or for recent brute force attacks, to use two-factor if the account doesn't already do so. Enabling this feature is highly recommended.", 'it-l10n-ithemes-security-pro' ); ?></p>
			</td>
		</tr>
		<tr class="itsec-two-factor-requires-email-provider">
			<th scope="row"><label for="itsec-two-factor-protect_vulnerable_site"><?php esc_html_e( 'Vulnerable Site Protection', 'it-l10n-ithemes-security-pro' ); ?></label></th>
			<td>
				<?php $form->add_checkbox( 'protect_vulnerable_site' ); ?>
				<label for="itsec-two-factor-protect_vulnerable_site"><?php esc_html_e( 'Enforce two-factor if the site is vulnerable.', 'it-l10n-ithemes-security-pro' ); ?></label>
				<p class="description"><?php esc_html_e( 'Require all users to use two-factor when logging in if the site is vulnerable, such as running outdated or software known to be vulnerable. Enabling this feature is highly recommended.', 'it-l10n-ithemes-security-pro' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="itsec-two-factor-disable_first_login"><?php esc_html_e( 'Disable on First Login', 'it-l10n-ithemes-security-pro' ); ?></label></th>
			<td>
				<?php $form->add_checkbox( 'disable_first_login' ); ?>
				<label for="itsec-two-factor-disable_first_login"><?php esc_html_e( "Don't require a two-factor code when a user first logs in.", 'it-l10n-ithemes-security-pro' ); ?></label>
				<p class="description"><?php esc_html_e( 'This simplifies the sign up flow for users that require two-factor to be enabled for their account.', 'it-l10n-ithemes-security-pro' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="itsec-two-factor-on_board_welcome"><?php esc_html_e( 'On-board Welcome Text', 'it-l10n-ithemes-security-pro' ); ?></label></th>
			<td>
				<?php $form->add_textarea( 'on_board_welcome' ); ?>
				<p class="description"><?php esc_html_e( 'Customize the text shown to users at the beginning of the Two-Factor On-Board flow.', 'it-l10n-ithemes-security-pro' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="itsec-two-factor-application_passwords_type"><?php esc_html_e( 'Application Passwords', 'it-l10n-ithemes-security-pro' ); ?></label></th>
			<td>
				<?php $form->add_user_groups( 'application_passwords_group', $this->id ); ?>
				<p class="description"><?php esc_html_e( 'Use Application Passwords to allow authentication without providing your actual password when using non-traditional login methods such as XML-RPC or the REST API. They can be easily revoked, and can never be used for traditional logins to your website.', 'it-l10n-ithemes-security-pro' ); ?></p>
			</td>
		</tr>
	</table>
<?php

	}
}

new ITSEC_Two_Factor_Settings_Page();
