# TODO 


- [ ] Modifier la base de données sur la table `T_USER_USR` pour donner l'auto-incrémentation des ID.
```sql 
ALTER TABLE T_USER_USR MODIFY COLUMN USR_ID INT AUTO_INCREMENT;
```
- [ ] Modifier toutes les tables pour donner l'auto-incrémentation des ID.
```sql 
ALTER TABLE <TABLE> MODIFY COLUMN <TBL>_ID INT AUTO_INCREMENT;
```
- [ ] Augmenter les tailles pour les champs `USR_EMAIL` (127) et `USR_PASSWORD`(255) dans la table `T_USER_USR`.
Dû au hachage du mot de passe, qui peut être très long, et mail qui peuvent être bien plus long.

## Log des ajouts/modifications

### 06-01-2025
- Ajout de l'authentification et de l'enregistrement de comptes.
- Modification de `security.yaml` pour mettre en place l'authentification en `app_user_provider`.
- Modification des Entity pour relier les clés par les entités correspondantes (et non plus par les id).
- Légère modulation de l'UI TWIG avec bouton de connection/déconnection en fonction du statut sur la page racine.

--> Lier le user (null) a un membre (s'il rejoint l'équipe qu'il créé)
