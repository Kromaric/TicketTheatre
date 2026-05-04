// cypress/e2e/reservation.cy.ts
describe("Parcours d'achat d'un billet", () => {
  beforeEach(() => {
    cy.login("jean.dupont@example.com", "password");
  });

  it("doit simuler un paiement réussi sans passer par Stripe", () => {
    // 1. On intercepte l'appel au Core Service qui initialise le paiement
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

    // 4. Arrivée sur la page Paiement
    cy.url().should("match", /\/paiement\/\d+/);

    // 5. On clique sur le bouton "Payer"
    // Au lieu d'ouvrir Stripe, Cypress va intercepter l'appel et répondre immédiatement
    cy.contains("avec Stripe", { matchCase: false }).click();

    // 6. On force la redirection vers la page de confirmation
    // Puisque Stripe est bloqué, on simule le retour de Stripe vers ton app
    cy.visit("/confirmation-paiement/7?session_id=test_success");

    // 7. Vérification finale
    cy.url().should("match", /\/confirmation-paiement\/\d+/);
    cy.contains("Confirmation", { matchCase: false }).should("be.visible");
  });
});
