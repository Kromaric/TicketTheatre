/// <reference types="cypress" />

declare namespace Cypress {
  interface Chainable {
    /**
     * Commande personnalisée pour connecter un utilisateur.
     * @param email L'adresse email de l'utilisateur
     * @param password Le mot de passe de l'utilisateur
     */
    login(email: string, password: string): Chainable<void>;
  }
}
