describe("Authentification", () => {
  it("doit permettre à un utilisateur de s'inscrire", () => {
    cy.visit("/register");

    // on remplit les infos du nouvel utilisateur
    cy.get('input[name="first_name"]').type("Jean");
    cy.get('input[name="last_name"]').type("Dupont");
    cy.get('input[name="email"]').type("jean.dupont@test.com");
    cy.get('input[name="password"]').type("MotDePasse123!");
    cy.get('input[name="password_confirm"]').type("MotDePasse123!");

    cy.get('[data-cy="login-submit-button"]').click();

    // on verifie qu'on est bien rediriger sur la page de login
    cy.url().should("eq", Cypress.config().baseUrl + "/login");
  });

  it("doit permettre de se connecter", () => {
    cy.visit("/login");

    // on remplit les infos d'un utilisateur existant
    cy.get('input[name="email"]').type("jean.dupont@example.com");
    cy.get('input[name="password"]').type("password");
    cy.get('[data-cy="login-submit-button"]').click();

    // on verifie qu'on est bien redirige sru la page d'accueil
    cy.url().should("eq", Cypress.config().baseUrl + "/");
  });
});
