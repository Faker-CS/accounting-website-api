<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Bienvenue chez ATFcompta+ – Votre compte entreprise est prêt !</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Arial', sans-serif; line-height: 1.6;">

  <!-- Main Container -->
  <div style="max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); overflow: hidden; border: 1px solid #e2e8f0;">

    <!-- Header -->
    <div style="background: linear-gradient(135deg, #2563eb, #1e40af); color: white; text-align: center; padding: 36px 20px;">
      <h1 style="margin: 0; font-size: 26px; font-weight: 700;">Votre compte entreprise est actif !</h1>
      <p style="margin: 8px 0 0; opacity: 0.9; font-size: 15px;">Bienvenue chez ATFcompta+ - Votre partenaire de gestion financière</p>
    </div>

    <!-- Content -->
    <div style="padding: 32px 24px; color: #1f2937;">
      <p style="margin-top: 0;">Cher(ère) <span style="font-weight: 600; color: #2563eb;">propriétaire de {{ $data['name'] }}</span>,</p>
      
      <p>Bienvenue chez ATFcompta+ ! Nous sommes ravis de vous accueillir. Votre compte entreprise a été créé avec succès et est prêt à être utilisé.</p>

      <!-- Credentials Box -->
      <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin: 20px 0;">
        <p style="margin: 0 0 10px; font-weight: 600; color: #2563eb;">Vos identifiants de connexion :</p>
        <ul style="margin: 0; padding-left: 20px;">
          <li style="margin-bottom: 8px;"><strong>Email :</strong> {{ $data['email'] }}</li>
          <li><strong>Mot de passe temporaire :</strong> {{ $data['password'] }}</li>
        </ul>
      </div>

      <p style="margin-bottom: 24px;">Pour des raisons de sécurité, nous recommandons de changer votre mot de passe après votre première connexion. Vous pouvez le faire dans les paramètres de votre compte.</p>

      <!-- CTA Button -->
      <div style="text-align: center; margin: 28px 0;">
        <a href="http://localhost:3031/login" style="display: inline-block; padding: 14px 32px; background: #2563eb; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); transition: all 0.3s ease;">
          Accéder à votre tableau de bord
        </a>
      </div>

      <p style="margin-bottom: 0; font-size: 15px; color: #6b7280;">Besoin d'aide ? Contactez notre équipe de support à <a href="mailto:support@atfcompta.com" style="color: #2563eb; text-decoration: none;">support@atfcompta.com</a>.</p>
    </div>

    <!-- Footer -->
    <div style="text-align: center; padding: 20px; background-color: #f9fafb; color: #6b7280; font-size: 13px; border-top: 1px solid #e5e7eb;">
      <p style="margin: 0;">© {{ date('Y') }} <strong>ATFcompta+</strong>. Tous droits réservés.</p>
      <p style="margin: 8px 0 0; font-size: 12px;">Votre partenaire de confiance en gestion financière</p>
    </div>
  </div>
</body>
</html>