import { useMemo, useState, useEffect } from "react";
import { Box, Button, Card, Flex, Input, Stack, Text, Spinner, Center } from "@chakra-ui/react";
import { useParams, useNavigate } from "react-router-dom";
import { TicketLabel } from "../components/TicketLabel";
import { coreService, type Seance } from "../services/core.service";
import { useAuth } from "../contexts/AuthContext";
import { toaster } from "../components/ui/toaster";

export default function Reservation() {
  const { seanceId } = useParams<{ seanceId: string }>();
  const navigate = useNavigate();
  const { user, isAuthenticated } = useAuth();
  const [seance, setSeance] = useState<Seance | null>(null);
  const [loading, setLoading] = useState(true);
  const [qty, setQty] = useState<number>(1);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    if (!isAuthenticated) {
      return;
    }

    if (seanceId) {
      loadSeance(parseInt(seanceId));
    } else {
      setLoading(false);
    }
  }, [seanceId, isAuthenticated]);

  const loadSeance = async (id: number) => {
    try {
      const data = await coreService.getSeance(id);
      if (!data) {
        throw new Error("Séance introuvable");
      }
      setSeance(data);
    } catch (error) {
      console.error('Erreur chargement séance:', error);
      toaster.error({
        title: "Erreur",
        description: "Impossible de charger la séance",
      });
      navigate("/programme");
    } finally {
      setLoading(false);
    }
  };

  const title = seance?.spectacle?.title ?? "Titre pièce";
  const price = seance?.price ?? 15;
  const availableSeats = seance ? (seance.remaining_seats || seance.available_seats) : 0;

  const total = useMemo(() => {
    const q = Number.isFinite(qty) ? qty : 1;
    return Math.max(1, q) * price;
  }, [qty, price]);

  const handleCheckout = async () => {
    if (!user || !seance) {
      toaster.error({
        title: "Erreur",
        description: "Informations manquantes",
      });
      return;
    }

    if (qty < 1 || qty > 10) {
      toaster.error({
        title: "Quantité invalide",
        description: "Veuillez choisir entre 1 et 10 places",
      });
      return;
    }

    if (qty > availableSeats) {
      toaster.error({
        title: "Places insuffisantes",
        description: `Seulement ${availableSeats} place(s) disponible(s)`,
      });
      return;
    }

    setSubmitting(true);
    try {
      // Préparer les données - ne pas inclure seats si non défini
      const reservationData: {
        user_id: number;
        seance_id: number;
        quantity: number;
        seats?: string[];
      } = {
        user_id: user.id,
        seance_id: seance.id,
        quantity: qty,
      };

      console.log('Création réservation avec données:', reservationData);

      const reservation = await coreService.createReservation(reservationData);

      console.log('Réservation créée avec succès:', reservation);

      toaster.success({
        title: "Réservation créée",
        description: `Référence: ${reservation.booking_reference}`,
      });

      // Rediriger vers la page de paiement
      navigate(`/paiement/${reservation.id}`);
    } catch (error) {
      console.error('Erreur complète création réservation:', error);
      
      const errorMessage = error instanceof Error 
        ? error.message 
        : "Impossible de créer la réservation";
      
      toaster.error({
        title: "Erreur de réservation",
        description: errorMessage,
      });
    } finally {
      setSubmitting(false);
    }
  };

  if (!isAuthenticated) {
    return (
      <Flex justify="center" align="center" minH="60vh" px={4}>
        <Card.Root w="full" maxW="520px" bg="yellow.500" color="black" borderRadius="lg" p="6">
          <Card.Header pb="3">
            <Card.Title textAlign="center" textTransform="uppercase">
              Connexion requise
            </Card.Title>
          </Card.Header>
          <Card.Body>
            <Text textAlign="center">
              Tu dois être connecté pour réserver des places.
            </Text>
          </Card.Body>
          <Card.Footer justifyContent="center" pt="4">
            <TicketLabel
              text="Se connecter"
              to="/login"
            />
          </Card.Footer>
        </Card.Root>
      </Flex>
    );
  }

  if (loading) {
    return (
      <Center minH="60vh">
        <Spinner size="xl" color="red.500" />
      </Center>
    );
  }

  return (
    <Flex justify="center" align="center" minH="60vh" px={4}>
      <Card.Root w="full" maxW="640px" bg="yellow.500" color="black" borderRadius="lg" p="6">
        <Card.Header pb="4">
          <Card.Title textAlign="center" textTransform="uppercase" fontSize="lg">
            Réservation
          </Card.Title>
          <Text textAlign="center" mt="2" fontWeight="semibold">
            {title}
          </Text>
          {seance && (
            <>
              <Text textAlign="center" fontSize="sm" mt="1">
                {new Date(seance.date_seance).toLocaleDateString("fr-FR", {
                  weekday: "long",
                  day: "2-digit",
                  month: "long",
                })}
                {" à "}
                {new Date(seance.date_seance).toLocaleTimeString("fr-FR", {
                  hour: "2-digit",
                  minute: "2-digit",
                })}
              </Text>
              {seance.hall && (
                <Text textAlign="center" fontSize="sm" opacity={0.8}>
                  {seance.hall.name}
                </Text>
              )}
            </>
          )}
        </Card.Header>

        <Card.Body>
          <Stack gap="5">
            <Flex justify="space-between" align="center">
              <Text fontWeight="semibold">Prix unitaire</Text>
              <Text>{price} €</Text>
            </Flex>

            {seance && (
              <Flex justify="space-between" align="center">
                <Text fontWeight="semibold">Places disponibles</Text>
                <Text>{availableSeats}</Text>
              </Flex>
            )}

            <Flex justify="space-between" align="center" gap={4}>
              <Box>
                <Text fontWeight="semibold">Nombre de places</Text>
                <Text fontSize="sm" opacity={0.8}>
                  (min 1, max {Math.min(10, availableSeats)})
                </Text>
              </Box>

              <Input
                type="number"
                min={1}
                max={Math.min(10, availableSeats)}
                value={qty}
                onChange={(e) => setQty(Math.max(1, Math.min(availableSeats, Number(e.target.value || 1))))}
                w="120px"
                bg="white"
                color="black"
              />
            </Flex>

            <Box h="1px" bg="blackAlpha.300" />

            <Flex justify="space-between" align="center">
              <Text fontWeight="bold" fontSize="lg">
                Total
              </Text>
              <Text fontWeight="bold" fontSize="lg">
                {total} €
              </Text>
            </Flex>

            <Button
              w="full"
              bg="red.800"
              color="white"
              _hover={{ bg: "red.700" }}
              onClick={handleCheckout}
              isLoading={submitting}
              loadingText="Création..."
            >
              Procéder au paiement
            </Button>

            <Button
              w="full"
              variant="ghost"
              onClick={() => navigate(-1)}
            >
              Retour
            </Button>
          </Stack>
        </Card.Body>
      </Card.Root>
    </Flex>
  );
}
