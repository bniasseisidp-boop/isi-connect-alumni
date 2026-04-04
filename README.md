# 🎓 ISI Connect - Alumni Hub Management

Bienvenue sur la plateforme **ISI Connect**, l'espace numérique dédié à la communauté des anciens élèves (Alumni) de l'ISI Suptech.

Cette application permet de gérer les invitations, les groupes de travail, et d'assurer une communication fluide entre les membres.

---

## 🏗 Structure du Projet

Le projet est divisé en deux parties principales :
1.  **`isi_connect_api`** : Le backend développé avec **Laravel 11** (PHP 8.2+).
2.  **`isi_connect_frontend`** : L'interface utilisateur développée avec **Vue.js 3** et **Vite**.

---

## 🛠 Installation Rapide (Plug & Play)

### 1. Pré-requis
-   Serveur local (**XAMPP** ou **Laragon**) avec MySQL.
-   **PHP 8.2+** et **Composer**.
-   **Node.js** et **NPM**.

### 2. Configuration du Backend (API)
- Allez dans le dossier `isi_connect_api`.
- Installez les dépendances :
  ```bash
  composer install
  ```
- Dupliquez le fichier `.env.example` et renommez-le en **`.env`**.
- Générez la clé de l'application :
  ```bash
  php artisan key:generate
  ```
- Créez une base de données MySQL nommée `isi_connect_db`.
- Configurez vos accès base de données dans le fichier `.env`.
- Lancez les migrations (pour créer toutes les tables) :
  ```bash
  php artisan migrate
  ```
- Lancez le serveur backend :
  ```bash
  php artisan serve --port=8001
  ```

### 3. Configuration du Frontend (Vue.js)
- Allez dans le dossier `isi_connect_frontend`.
- Installez les dépendances :
  ```bash
  npm install
  ```
- Lancez le serveur de développement :
  ```bash
  npm run dev
  ```

---

## 🔐 Sécurité & Emails
- Pour envoyer de vrais emails, configurez vos identifiants SMTP dans le fichier `.env` de l'API.
- Un nouveau compte créé via invitation **doit obligatoirement** changer son mot de passe lors de sa première connexion pour sécuriser son accès.

---

## 👨‍💻 Technologie Stack
- **Framework Backend** : Laravel 11
- **Framework Frontend** : Vue.js 3 / Vite
- **Base de données** : MySQL
- **Stylisation** : TailwindCSS / Modern Glassmorphism Logic

---

© 2024 - ISI Suptech Alumni Management System.
