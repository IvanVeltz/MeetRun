# 🏃‍♀️ Meet & Run – Plateforme de mise en relation pour coureurs

**Meet & Run** est une application web destinée aux amateurs et passionnés de course à pied. La plateforme permet aux utilisateurs de se rencontrer, de participer à des courses et d’échanger via un forum communautaire.

---

## 🛠️ Technologies utilisées

| Technologie      | Version         |
|------------------|-----------------|
| PHP              | 8.3            |
| Symfony          | 7.2            |
| MySQL            | 8.0            |
| HTML             | 5        |
| CSS              | 3              |
| JavaScript       | ES6+            |

---

## 🚀 Fonctionnalités principales

- 🔐 **Inscription / Connexion**
  - Inscription classique par e-mail avec validation
  - Connexion via Google OAuth

- 🗓️ **Gestion des courses**
  - Création, modification et suppression de courses (CRUD)
  - Inscription des utilisateurs aux courses

- 👥 **Réseau social intégré**
  - Système de follow/unfollow entre utilisateurs
  - Consultation de profils publics

- 🔍 **Recherche avancée de coureurs**
  - Filtres multiples (âge, sexe, niveau, localisation…)
  - Filtrage dynamique en AJAX sans rechargement de page
 
- 💬 **Forum**
  - Création de topics
  - Organisation par catégories

---

## 🧪 Fonctionnalités à venir

- 🤝 **Suggestions de profils compatibles**
  - Recommandation basée sur le niveau, la localisation et les habitudes de course

- 💬 **Messagerie privée**
  - Envoi de messages entre utilisateurs
  - Système de notification intégré

---

## 📦 Installation du projet en local

```bash
git clone https://github.com/IvanVeltz/MeetRun.git
cd meet-and-run
composer install
npm install && npm run dev
cp .env .env.local
# Configurez votre base de données dans .env.local
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
symfony server:start
```

---

## 👤 Auteur

**Ivan Veltz**
