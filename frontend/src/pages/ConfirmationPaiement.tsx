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
    const paymentIntent = searchParams.get("payment_intent");
    const status = searchParams.get("redirect_status");

    if (status === "succeeded" && paymentIntent && reservationId) {
      confirmPayment(parseInt(reservationId), paymentIntent);
    } else {
      setLoading(false);
      toaster.error({
        title: "Erreur",
        description: "Informations de paiement manquantes",
      });
    }
  }, [reservationId, searchParams]);

  const confirmPayment = async (resId: number, paymentIntentId: string) => {
    try {
      await coreService.confirmPayment(resId, paymentIntentId);
      setSuccess(true);
      toaster.success({
        title: "Paiement confirmé !",
        description: "Votre réservation est confirmée",
      });
    } catch (error) {
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
                  ? "Votre réservation a été confirmée avec succès"
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
