import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
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
  Grid,
} from "@chakra-ui/react";
import { coreService, type Reservation } from "../services/core.service";
import { useAuth } from "../contexts/AuthContext";
import { toaster } from "../components/ui/toaster";

const statusColors: Record<string, string> = {
  pending: "orange",
  confirmed: "green",
  cancelled: "red",
  expired: "gray",
};

const statusLabels: Record<string, string> = {
  pending: "En attente",
  confirmed: "Confirmée",
  cancelled: "Annulée",
  expired: "Expirée",
};

const paymentStatusLabels: Record<string, string> = {
  pending: "En attente",
  paid: "Payé",
  refunded: "Remboursé",
  failed: "Échoué",
};

export default function MesReservations() {
  const { user, isAuthenticated } = useAuth();
  const navigate = useNavigate();
  const [reservations, setReservations] = useState<Reservation[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!isAuthenticated) {
      navigate("/login");
      return;
    }

    if (user) {
      loadReservations();
    }
  }, [user, isAuthenticated]);

  const loadReservations = async () => {
    if (!user) return;

    try {
      const data = await coreService.getUserReservations(user.id);
      console.log(data)
      setReservations(data);
    } catch (error) {
      console.error('Erreur chargement réservations:', error);
      toaster.error({
        title: "Erreur",
        description: "Impossible de charger vos réservations",
      });
    } finally {
      setLoading(false);
    }
  };

  const handlePay = (reservationId: number) => {
    navigate(`/paiement/${reservationId}`);
  };

  const handleCancel = async (reservationId: number) => {
    if (confirm("Êtes-vous sûr de vouloir annuler cette réservation ?")) {
      try {
        await coreService.cancelReservation(reservationId, "Annulation par l'utilisateur");
        toaster.success({
          title: "Réservation annulée",
          description: "Votre réservation a été annulée avec succès",
        });
        loadReservations();
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

  return (
    <Box w="full" maxW="1200px" mx="auto" py={8} px={4}>
      <Flex justify="space-between" align="center" mb={6}>
        <Heading size="xl">Mes réservations</Heading>
        <Button
          bg="red.800"
          color="white"
          _hover={{ bg: "red.700" }}
          onClick={() => navigate("/programme")}
        >
          Nouvelle réservation
        </Button>
      </Flex>

      {reservations.length === 0 ? (
        <Center minH="50vh">
          <Stack gap={4} textAlign="center">
            <Text fontSize="xl" color="gray.500">
              Vous n'avez aucune réservation
            </Text>
            <Button
              bg="red.800"
              color="white"
              _hover={{ bg: "red.700" }}
              onClick={() => navigate("/programme")}
            >
              Voir le programme
            </Button>
          </Stack>
        </Center>
      ) : (
        <Grid templateColumns={{ base: "1fr", md: "repeat(2, 1fr)" }} gap={6}>
          {reservations.map((reservation) => (
            <Card.Root key={reservation.id} bg="gray.800">
              <Card.Body>
                <Stack gap={3}>
                  <Flex justify="space-between" align="start">
                    <Box>
                      <Badge
                        colorScheme={statusColors[reservation.status]}
                        mb={2}
                      >
                        {statusLabels[reservation.status]}
                      </Badge>
                      <Text fontSize="xs" color="gray.400">
                        Ref: {reservation.booking_reference}
                      </Text>
                    </Box>
                    <Badge
                      colorScheme={reservation.payment_status === 'paid' ? 'green' : 'orange'}
                    >
                      {paymentStatusLabels[reservation.payment_status]}
                    </Badge>
                  </Flex>

                  {reservation.seance?.spectacle && (
                    <>
                      <Text fontSize="lg" fontWeight="bold" color="red.400">
                        {reservation.seance.spectacle.title}
                      </Text>
                      
                      <Text color="gray.300">
                        {new Date(reservation.seance.date_seance).toLocaleDateString("fr-FR", {
                          weekday: "long",
                          day: "2-digit",
                          month: "long",
                          year: "numeric",
                        })}
                      </Text>

                      <Text color="gray.300">
                        {new Date(reservation.seance.date_seance).toLocaleTimeString("fr-FR", {
                          hour: "2-digit",
                          minute: "2-digit",
                        })}
                      </Text>

                      {reservation.seance.hall && (
                        <Text color="gray.400" fontSize="sm">
                          📍 {reservation.seance.hall.name}
                        </Text>
                      )}
                    </>
                  )}

                  <Flex justify="space-between" borderTop="1px" borderColor="gray.700" pt={3}>
                    <Text>
                      {reservation.quantity} place{reservation.quantity > 1 ? 's' : ''}
                    </Text>
                    <Text fontWeight="bold" fontSize="xl" color="red.400">
                      {reservation.total_price} €
                    </Text>
                  </Flex>

                  {reservation.seats && reservation.seats.length > 0 && (
                    <Text fontSize="sm" color="gray.400">
                      Places: {reservation.seats.join(", ")}
                    </Text>
                  )}

                  {reservation.expires_at && reservation.status === 'pending' && (
                    <Text fontSize="sm" color="orange.400">
                      ⏱ Expire le {new Date(reservation.expires_at).toLocaleString("fr-FR")}
                    </Text>
                  )}

                  {reservation.confirmed_at && (
                    <Text fontSize="sm" color="green.400">
                      ✓ Confirmée le {new Date(reservation.confirmed_at).toLocaleString("fr-FR")}
                    </Text>
                  )}

                  <Stack gap={2}>
                    {reservation.status === 'pending' && reservation.payment_status === 'pending' && (
                      <Button
                        w="full"
                        bg="green.600"
                        color="white"
                        _hover={{ bg: "green.500" }}
                        onClick={() => handlePay(reservation.id)}
                      >
                        Payer maintenant
                      </Button>
                    )}

                    {reservation.status === 'confirmed' && (
                      <Button
                        w="full"
                        bg="blue.600"
                        color="white"
                        _hover={{ bg: "blue.500" }}
                        onClick={() => {
                          toaster.info({
                            title: "Billet",
                            description: "Fonctionnalité d'affichage du billet à venir",
                          });
                        }}
                      >
                        Voir le billet
                      </Button>
                    )}

                    {(reservation.status === 'pending' || reservation.status === 'confirmed') && (
                      <Button
                        w="full"
                        variant="outline"
                        colorScheme="red"
                        onClick={() => handleCancel(reservation.id)}
                      >
                        Annuler
                      </Button>
                    )}
                  </Stack>
                </Stack>
              </Card.Body>
            </Card.Root>
          ))}
        </Grid>
      )}
    </Box>
  );
}
