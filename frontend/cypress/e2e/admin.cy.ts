describe("Espace Administrateur", () => {
  it('doit bloquer le contenu et afficher "Accès refusé" si non connecté', () => {
    cy.visit("/admin");
    cy.url().should("include", "/admin");

    // On verifie qu'il y a bien une erreur
    cy.contains("Accès refusé").should("be.visible");
    cy.contains("Vous n'avez pas les permissions nécessaires.").should(
      "be.visible",
    );

    // on test le bouton retour
    cy.contains("Retour à l'accueil").click();

    // on est bien de retour a la racine
    cy.url().should("eq", Cypress.config().baseUrl + "/");
  });

  it("doit afficher le tableau de bord pour un administrateur", () => {
    cy.login("admin@tickettheatre.com", "password");
    //on se connecte a la page admin
    cy.visit("/admin");

    // On verifie qu'on peut y avoir accès
    cy.contains("Administration").should("be.visible");

    //  les catégories sont bien présente
    cy.contains("Catégories").should("be.visible");
    cy.contains("Gérer les catégories de spectacles").should("be.visible");

    // On clique pour aller sur les catégories
    cy.contains("Catégories").click();
    cy.url().should("include", "/admin/categories");
  });
});
