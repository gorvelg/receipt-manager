# Application pour gérer des tickets de caisse.

## Objectifs

L'application offre la possibilité de télécharger des tickets de caisse, permettant ainsi le partage mensuel du montant total. Cette fonctionnalité vise à optimiser la gestion budgétaire du foyer.

## Fonctionnalités

- **Upload d'image/photo**
- **Stockage des tickets dans une base de données**
- **Calcul du montant total des tickets**
- **Division mensuelle du montant total**: Le montant est divisé par deux chaque mois. Ou par le nombre d'utilisateurs.
- Possibilité de ne pas prendre en compte certains tickets : Les utilisateurs peuvent exclure le montant d'un ticket et le rajouter uniquement pour leur part à payer.
- **Notification par mail**: A la fin de chaque mois, chaque utilisateur recevra une notification par mail l'informant du montant qu'il doit payer.
- **Notification Push**
- **Historique mensuel**: Les utilisateurs peuvent consulter l'historique mensuel de leurs dépenses.
- **Compte individuel**: Les utilisateurs ont des comptes individuels et peuvent avoir accès aux tickets de caisse.
- **Gestion des tickets de caisse**: Chaque utilisateur peut supprimer les tickets de caisse dont ils sont propriétaires.
- **Back Office**: CRUD des utilisateurs, foyers et tickets.
- ***Extraction de Texte (OCR)*** *: L'application utilise une API OCR pour extraire automatiquement les informations textuelles des tickets de caisse. (Optionnel)*

## Technologies utilisées
- **Langage de programmation**: PHP 8.2, Javascript
- **Framework**: Symfony 6.4
- **Serveur Web**: Apache2

## Base de données

### Table: User
| Column   | Type     | Constraints |
|----------|----------|-------------|
| id       | integer  | primary key |
| username | varchar  |             |
| role     | varchar  |             |
| email    | varchar  |             |

### Table: Ticket
| Column     | Type      | Constraints |
|------------|-----------|-------------|
| id         | integer   | primary key |
| title      | varchar   |             |
| photo      | image     |             |
| amount     | float     |             |
| user_id    | integer   |             |
| created_at | timestamp |             |

### Table: Foyer
| Column | Type    | Constraints |
|--------|---------|-------------|
| id     | integer | primary key |
| name   | varchar |             |

### Table: Foyer_User
| Column  | Type    | Constraints |
|---------|---------|-------------|
| user_id | integer |             |
| foyer_id| integer |             |


Ref: ticket.user_id > user.id // many-to-one
Ref: foyer.id > foyer_user.foyer_id
Ref: user.id > foyer_user.user_id
