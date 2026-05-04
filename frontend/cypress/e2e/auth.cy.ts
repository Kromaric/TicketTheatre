describe("Authentification", () => {
  it("doit permettre à un utilisateur de s'inscrire", () => {
    cy.visit("/register");

    // Ajuste les sélecteurs selon tes `name` ou `data-cy` dans Inscription.tsx
    cy.get('input[name="first_name"]').type("Jean");
    cy.get('input[name="last_name"]').type("Dupont");
    cy.get('input[name="email"]').type("jean.dupont@test.com");
    cy.get('input[name="password"]').type("MotDePasse123!");
    cy.get('input[name="password_confirm"]').type("MotDePasse123!");

    cy.get('[data-cy="login-submit-button"]').click();

    // Selon ton AuthContext, l'inscription connecte l'utilisateur directement.
    // Vérifie ce qu'il se passe dans ton UI (redirection vers home ?)
    cy.url().should("eq", Cypress.config().baseUrl + "/login");
  });

  it("doit permettre de se connecter", () => {
    cy.visit("/login");

    cy.get('input[name="email"]').type("jean.dupont@example.com");
    cy.get('input[name="password"]').type("password");
    cy.get('[data-cy="login-submit-button"]').click();
    // Vérifie la redirection post-login
    cy.url().should("eq", Cypress.config().baseUrl + "/");
  });
});
