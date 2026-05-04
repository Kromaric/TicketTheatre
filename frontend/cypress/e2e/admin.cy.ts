describe("Espace Administrateur", () => {
  it('doit bloquer le contenu et afficher "Accès refusé" si non connecté', () => {
    cy.visit("/admin");

    // On s'assure qu'on est bien resté sur la page admin (pas de redirection)
    cy.url().should("include", "/admin");

    // On vérifie que ta carte d'erreur s'affiche
    cy.contains("Accès refusé").should("be.visible");
    cy.contains("Vous n'avez pas les permissions nécessaires.").should(
      "be.visible",
    );

    // On teste ton bouton de retour
    cy.contains("Retour à l'accueil").click();

    // On vérifie qu'on est bien revenu à la racine
    cy.url().should("eq", Cypress.config().baseUrl + "/");
  });

  it("doit afficher le tableau de bord pour un administrateur", () => {
    // Note : Il faut t'assurer que ton `cy.login` renvoie bien un user avec `role: 'admin'`
    cy.login("admin@tickettheatre.com", "password");

    cy.visit("/admin");

    // On vérifie le titre principal
    cy.contains("Administration").should("be.visible");

    // On vérifie la présence d'une de tes sections (ex: Catégories)
    cy.contains("Catégories").should("be.visible");
    cy.contains("Gérer les catégories de spectacles").should("be.visible");

    // On clique sur la carte pour aller vers /admin/categories
    // Dans ton code, le onClick est sur le Card.Root
    cy.contains("Catégories").click();
    cy.url().should("include", "/admin/categories");
  });
});
