import { useEffect, useState } from "react";
import { useParams, useNavigate, useSearchParams } from "react-router-dom";
import {
  Box,
  Button,
  Heading,
  Text,
  Card,
  Spinner,
  Center,
  Stack,
} from "@chakra-ui/react";
import { coreService } from "../services/core.service";
import { toaster } from "../components/ui/toaster";

export default function ConfirmationPaiement() {
  const { reservationId } = useParams<{ reservationId: string }>();
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [success, setSuccess] = useState(false);

  useEffect(() => {
    const sessionId = searchParams.get("session_id");

    if (sessionId && reservationId) {
      // Le paiement Stripe est validé, confirmer la réservation
      confirmPayment(parseInt(reservationId), sessionId);
    } else {
      setLoading(false);
      toaster.error({
        title: "Erreur",
        description: "Informations de paiement manquantes",
      });
    }
  }, [reservationId, searchParams]);

  const confirmPayment = async (resId: number, sessionId: string) => {
    try {
      // Confirmer le paiement avec la session Stripe
      await coreService.confirmPaymentManual(resId, sessionId);
      setSuccess(true);
      toaster.success({
        title: "Paiement confirmé !",
        description: "Votre réservation est confirmée",
      });
    } catch (error) {
      console.error('Erreur confirmation:', error);
      toaster.error({
        title: "Erreur",
        description: error instanceof Error ? error.message : "Erreur lors de la confirmation",
      });
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <Center minH="70vh">
        <Stack gap={4} textAlign="center">
          <Spinner size="xl" color="red.500" />
          <Text>Confirmation du paiement en cours...</Text>
        </Stack>
      </Center>
    );
  }

  return (
    <Box w="full" maxW="800px" mx="auto" py={8} px={4}>
      <Card.Root bg={success ? "green.800" : "red.800"}>
        <Card.Body>
          <Center>
            <Stack gap={4} textAlign="center">
              <Heading size="xl">
                {success ? "✓ Paiement réussi !" : "✗ Échec du paiement"}
              </Heading>
              <Text>
                {success
                  ? "Votre réservation a été confirmée avec succès. Vous allez recevoir un email de confirmation."
                  : "Une erreur est survenue lors du traitement de votre paiement"}
              </Text>
              <Button onClick={() => navigate("/mes-reservations")}>
                Voir mes réservations
              </Button>
            </Stack>
          </Center>
        </Card.Body>
      </Card.Root>
    </Box>
  );
}
