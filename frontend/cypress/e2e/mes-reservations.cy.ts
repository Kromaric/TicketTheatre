describe("Parcours d'achat d'un billet", () => {
  beforeEach(() => {
    // Connexion avant chaque test
    cy.login("jean.dupont@example.com", "password");
  });

  it("verifier qu'il y a bien des réservations", () => {
    cy.contains("Mes réservations", { matchCase: false }).first().click();
    cy.get('button:contains("Voir le billet")', { timeout: 10000 }).should(
      "have.length.at.least",
      1,
    );
  });
});
