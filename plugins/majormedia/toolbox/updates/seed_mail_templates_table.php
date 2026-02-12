<?php namespace MajorMedia\ToolBox\Updates;

use DB;
use Seeder;

class SeedMailTemplatesTable extends Seeder
{
  public function run()
  {
    DB::table('system_mail_templates')->truncate();
    DB::table('system_mail_templates')->insert([
      'code' => 'majormedia.toolbox::mail.admin_new_message',
      'layout_id' => 3,
      'is_custom' => 1,
      'subject' => 'Vous avez reçu un nouveau message du site web', 'description' => 'Quand Une nouvelle demande depuis le formulaire du site',
      'content_html' => 'Bonjour Admin,

Voici le détail de la demande de devis reçue sur le site:

* Service: {{form.service}}
* Nom complet: {{form.full_name}}
* E-mail: {{form.email}}
* Société: {{form.company}}
* Téléphone: {{form.phone}}
* Site web: {{form.website}}
* Sujet: {{form.subject}}
* Message: {{form.message}}
* Fichiers joints:
{% for f in form.files %}
* {{ f.getPath() }}
{% endfor %}

Bonne journée.',
    ]);
    DB::table('system_mail_templates')->insert([
      'code' => 'rainlab.user::mail.activate',
      'layout_id' => 3,
      'is_custom' => 1,
      'subject' => 'Confirmez votre compte',
      'description' => 'Activer un nouvel utilisateur',
      'content_html' => 'Bonjour {{ name }}

Nous devons vérifier qu\'il s\'agit bien de votre adresse e-mail.

Veuillez cliquer sur le lien ci-dessous pour confirmer votre compte:

{% partial \'button\' url=link type=\'positive\' body %}
Confirmer le compte
{% endpartial %}'
    ]);
    DB::table('system_mail_templates')->insert([
      'code' => 'rainlab.user::mail.welcome',
      'layout_id' => 3,
      'is_custom' => 1,
      'subject' => 'Votre compte a été confirmé!',
      'description' => 'L\'utilisateur a confirmé son compte',
      'content_html' => 'Bonjour {{ name }}

Ceci est un message pour vous informer que votre compte a été activé avec succès.'
    ]);
    DB::table('system_mail_templates')->insert([
      'code' => 'rainlab.user::mail.restore',
      'layout_id' => 3,
      'is_custom' => 1,
      'subject' => 'Réinitialisation du mot de passe',
      'description' => 'User requests a password reset',
      'content_html' => 'Bonjour {{ name }}

Quelqu\'un a demandé la réinitialisation du mot de passe de votre compte, si ce n\'est pas vous, veuillez ignorer cet e-mail.

Utilisez ce code d\'activation pour restaurer votre mot de passe:

{% partial \'promotion\' body %}
{{ code }}
{% endpartial %}

Vous pouvez utiliser le lien suivant:

{% partial \'button\' url=link type=\'positive\' body %}
Restore password
{% endpartial %}

{% partial \'subcopy\' body %}
    ** Ceci est un message automatique. Veuillez ne pas y répondre. **
{% endpartial %}'
    ]);
    DB::table('system_mail_templates')->insert([
      'code' => 'rainlab.user::mail.new_user',
      'layout_id' => 3,
      'is_custom' => 1,
      'subject' => 'Un nouvel utilisateur s\'est inscrit',
      'description' => 'Informer les administrateurs d\'une nouvelle inscription',
      'content_html' => 'Bonjour {{ name }}

Un nouvel utilisateur vient de s\'inscrire. Voici les détails:

- ID: `{{ id }}`
- Nom: `{{ name }}`
- Email: `{{ email }}`'
    ]);
    DB::table('system_mail_templates')->insert([
      'code' => 'rainlab.user::mail.reactivate',
      'layout_id' => 3,
      'is_custom' => 1,
      'subject' => 'Bienvenu(e) ! Votre compte a été réactivé',
      'description' => 'L\'utilisateur a réactivé son compte',
      'content_html' => 'Bienvenu(e) {{ name }}

Ceci est un message pour vous informer que vous vous êtes connecté avec succès et que vous avez réactivé votre compte.

Si vous ne l\'avez pas demandé, nous vous suggérons de changer votre mot de passe avant de désactiver à nouveau votre compte.'
    ]);
    DB::table('system_mail_templates')->insert([
      'code' => 'rainlab.user::mail.invite',
      'layout_id' => 3,
      'is_custom' => 1,
      'subject' => 'Un compte a été créé pour vous',
      'description' => 'Inviter un nouvel utilisateur sur le site Web',
      'content_html' => 'Bonjour {{ name }}

Un compte utilisateur a été créé pour vous. Veuillez utiliser l\'identifiant et le mot de passe suivants pour vous connecter:

{% partial \'panel\' body %}
- Login: `{{ login }}`
- Mot de passe: `{{ password|raw }}`
{% endpartial %}

Après vous connecter, vous devez changer votre mot de passe dès que possible.'
    ]);
    DB::table('system_mail_templates')->insert([
      'code' => 'backend::mail.invite',
      'layout_id' => 3,
      'is_custom' => 1,
      'subject' => 'Bienvenu(e) à {{ appName | raw }}',
      'description' => 'Inviter un nouvel administrateur sur le site',
      'content_html' => 'Salut {{ name }}

Un compte utilisateur a été créé pour vous en **{{ appName }}**.

{% partial \'panel\' body %}
- Login: `{{ login ?: \'sample\' }}`
- Mot de passe: `{{ (password ?: \'********\') | raw }}`
{% endpartial %}

Vous pouvez utiliser le lien suivant pour vous connecter:

{% partial \'button\' url=link body %}
    Connectez-vous à la zone d\'administration
{% endpartial %}

Après vous être connecté, vous devez changer votre mot de passe en cliquant sur votre nom dans le coin supérieur droit de la zone d\'administration.'
    ]);
    DB::table('system_mail_templates')->insert([
      'code' => 'backend::mail.restore',
      'layout_id' => 3,
      'is_custom' => 1,
      'subject' => 'Réinitialisation du mot de passe',
      'description' => 'Réinitialiser un mot de passe administrateur',
      'content_html' => 'Bonjour {{ name }}

Quelqu\'un a demandé la réinitialisation du mot de passe de votre compte, si ce n\'est pas vous, veuillez ignorer cet e-mail.

Vous pouvez utiliser le lien suivant pour restaurer votre mot de passe:

{% partial \'button\' url=link type=\'positive\' body %}
Restaurer le mot de passe
{% endpartial %}'
    ]);
  }


}