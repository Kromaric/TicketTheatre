// cypress/e2e/reservation.cy.ts
describe("Parcours d'achat d'un billet", () => {
  beforeEach(() => {
    cy.login("jean.dupont@example.com", "password");
  });

  it("doit simuler un paiement réussi sans passer par Stripe", () => {
    // Interception l'appel au Core Service qui initialise le paiement
    cy.intercept("POST", "**/api/reservations/*/initiate-payment", {
      statusCode: 200,
      body: {
        success: true,
        data: {
          checkout_url:
            "http://localhost:5173/confirmation-paiement/7?session_id=mock_session_id",
        },
      },
    }).as("initiatePayment");

    cy.visit("/programme");
    cy.contains("Après-demain", { matchCase: false }).first().click();
    cy.contains("Réserver", { matchCase: false }).first().click();
    cy.contains("Réserver", { matchCase: false }).first().click();
    cy.contains("Procéder", { matchCase: false }).first().click();

    // Page paiement
    cy.url().should("match", /\/paiement\/\d+/);

    // On intercepte l'appel au lieu d'aller sur stripe et la réponse
    cy.contains("avec Stripe", { matchCase: false }).click();

    // on redirecte vers la page de paiement
    cy.visit("/confirmation-paiement/7?session_id=test_success");

    // le paiement est bien fais
    cy.url().should("match", /\/confirmation-paiement\/\d+/);
    cy.contains("Confirmation", { matchCase: false }).should("be.visible");
  });
});
