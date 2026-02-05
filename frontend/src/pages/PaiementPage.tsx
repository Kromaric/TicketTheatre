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
  Flex,
  Badge,
} from "@chakra-ui/react";
import { coreService, type Reservation } from "../services/core.service";
import { useAuth } from "../contexts/AuthContext";
import { toaster } from "../components/ui/toaster";

export default function PaiementPage() {
  const { reservationId } = useParams<{ reservationId: string }>();
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const { user, isAuthenticated } = useAuth();
  const [reservation, setReservation] = useState<Reservation | null>(null);
  const [loading, setLoading] = useState(true);
  const [redirecting, setRedirecting] = useState(false);

  useEffect(() => {
    if (!isAuthenticated) {
      navigate("/login");
      return;
    }

    const canceled = searchParams.get("canceled");
    if (canceled) {
      toaster.error({
        title: "Paiement annulé",
        description: "Vous avez annulé le paiement",
      });
    }

    if (reservationId) {
      loadReservation(parseInt(reservationId));
    }
  }, [reservationId, isAuthenticated, searchParams]);

  const loadReservation = async (id: number) => {
    try {
      const data = await coreService.getReservation(id);
      
      if (!data) {
        throw new Error("Réservation introuvable");
      }

      if (data.user_id !== user?.id) {
        throw new Error("Cette réservation ne vous appartient pas");
      }

      setReservation(data);
    } catch (error) {
      console.error('Erreur chargement réservation:', error);
      toaster.error({
        title: "Erreur",
        description: error instanceof Error ? error.message : "Impossible de charger la réservation",
      });
      navigate("/mes-reservations");
    } finally {
      setLoading(false);
    }
  };

  const handlePayment = async () => {
    if (!reservation || !user) return;

    setRedirecting(true);
    try {
      const paymentData = await coreService.initiatePayment(
        reservation.id,
        user.email
      );

      // Rediriger vers Stripe Checkout
      window.location.href = paymentData.checkout_url;
    } catch (error) {
      toaster.error({
        title: "Erreur de paiement",
        description: error instanceof Error ? error.message : "Impossible d'initier le paiement",
      });
      setRedirecting(false);
    }
  };

  const handleCancel = async () => {
    if (!reservation) return;

    if (confirm("Êtes-vous sûr de vouloir annuler cette réservation ?")) {
      try {
        await coreService.cancelReservation(reservation.id, "Annulation par l'utilisateur");
        toaster.success({
          title: "Réservation annulée",
          description: "Votre réservation a été annulée",
        });
        navigate("/mes-reservations");
      } catch (error) {
        toaster.error({
          title: "Erreur",
          description: "Impossible d'annuler la réservation",
        });
      }
    }
  };

  if (loading) {
    return (
      <Center minH="70vh">
        <Spinner size="xl" color="red.500" />
      </Center>
    );
  }

  if (!reservation || !reservation.seance) {
    return (
      <Center minH="70vh">
        <Text>Réservation introuvable</Text>
      </Center>
    );
  }

  if (reservation.payment_status === 'paid') {
    return (
      <Box w="full" maxW="800px" mx="auto" py={8} px={4}>
        <Card.Root bg="green.800">
          <Card.Body>
            <Center>
              <Stack gap={4} textAlign="center">
                <Heading size="xl">✓ Paiement déjà effectué</Heading>
                <Text>Cette réservation a déjà été payée</Text>
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

  const isExpired = reservation.expires_at && new Date(reservation.expires_at) < new Date();
  if (isExpired) {
    return (
      <Box w="full" maxW="800px" mx="auto" py={8} px={4}>
        <Card.Root bg="red.800">
          <Card.Body>
            <Center>
              <Stack gap={4} textAlign="center">
                <Heading size="xl">⚠ Réservation expirée</Heading>
                <Text>Cette réservation a expiré. Veuillez créer une nouvelle réservation.</Text>
                <Button onClick={() => navigate("/programme")}>
                  Voir le programme
                </Button>
              </Stack>
            </Center>
          </Card.Body>
        </Card.Root>
      </Box>
    );
  }

  return (
    <Box w="full" maxW="800px" mx="auto" py={8} px={4}>
      <Heading size="xl" mb={6}>Paiement de votre réservation</Heading>

      <Card.Root bg="gray.800" mb={6}>
        <Card.Body>
          <Stack gap={4}>
            <Flex justify="space-between" align="center">
              <Text fontSize="sm" color="gray.400">Référence</Text>
              <Badge colorScheme="blue" fontSize="md" px={3} py={1}>
                {reservation.booking_reference}
              </Badge>
            </Flex>

            {reservation.seance.spectacle && (
              <Box>
                <Text fontSize="2xl" fontWeight="bold" color="red.400">
                  {reservation.seance.spectacle.title}
                </Text>
                <Text color="gray.400">
                  {new Date(reservation.seance.date_seance).toLocaleDateString("fr-FR", {
                    weekday: "long",
                    day: "2-digit",
                    month: "long",
                    year: "numeric",
                  })}
                  {" à "}
                  {new Date(reservation.seance.date_seance).toLocaleTimeString("fr-FR", {
                    hour: "2-digit",
                    minute: "2-digit",
                  })}
                </Text>
              </Box>
            )}

            <Flex justify="space-between">
              <Text>Nombre de places</Text>
              <Text fontWeight="bold">{reservation.quantity}</Text>
            </Flex>

            <Flex justify="space-between">
              <Text>Prix unitaire</Text>
              <Text>{(reservation.total_price / reservation.quantity).toFixed(2)} €</Text>
            </Flex>

            {reservation.expires_at && (
              <Box borderTop="1px" borderColor="gray.700" pt={4}>
                <Text fontSize="sm" color="orange.400">
                  ⏱ Expire le {new Date(reservation.expires_at).toLocaleString("fr-FR")}
                </Text>
              </Box>
            )}

            <Box borderTop="1px" borderColor="gray.700" pt={4}>
              <Flex justify="space-between" align="center">
                <Text fontSize="xl" fontWeight="bold">Total à payer</Text>
                <Text fontSize="3xl" fontWeight="bold" color="red.400">
                  {reservation.total_price} €
                </Text>
              </Flex>
            </Box>
          </Stack>
        </Card.Body>
      </Card.Root>

      <Card.Root bg="gray.800" mb={6}>
        <Card.Body>
          <Stack gap={4}>
            <Heading size="md">Paiement sécurisé</Heading>
            <Text color="gray.400">
              Vous allez être redirigé vers Stripe pour effectuer le paiement de manière sécurisée.
            </Text>

            <Button
              w="full"
              size="lg"
              bg="green.600"
              color="white"
              _hover={{ bg: "green.500" }}
              onClick={handlePayment}
              isLoading={redirecting}
              loadingText="Redirection..."
            >
              Payer {reservation.total_price} € avec Stripe
            </Button>

            <Button
              w="full"
              variant="outline"
              colorScheme="red"
              onClick={handleCancel}
              isDisabled={redirecting}
            >
              Annuler la réservation
            </Button>
          </Stack>
        </Card.Body>
      </Card.Root>
    </Box>
  );
}
