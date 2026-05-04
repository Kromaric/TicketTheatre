describe("Parcours d'achat d'un billet", () => {
  beforeEach(() => {
    // Connexion avant achat
    cy.login("jean.dupont@example.com", "password");
  });

  it("verifier qu'il y a bien des réservations", () => {
    cy.contains("Mes réservations", { matchCase: false }).first().click();
    cy.get('button:contains("Voir le billet")').should("have.length", 3);
  });
});
