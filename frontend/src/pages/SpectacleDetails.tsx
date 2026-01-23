import { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import {
  Box,
  Button,
  Flex,
  Grid,
  Heading,
  Image,
  Text,
  Badge,
  Card,
  Spinner,
  Center,
  Stack,
} from "@chakra-ui/react";
import { coreService, type Spectacle, type Seance } from "../services/core.service";
import { useAuth } from "../contexts/AuthContext";
import { toaster } from "../components/ui/toaster";
import theatreImg from "../assets/theatre.png";

export default function SpectacleDetails() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { isAuthenticated } = useAuth();
  const [spectacle, setSpectacle] = useState<Spectacle | null>(null);
  const [seances, setSeances] = useState<Seance[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (id) {
      loadSpectacle(parseInt(id));
    }
  }, [id]);

  const loadSpectacle = async (spectacleId: number) => {
    try {
      const [spectacleData, seancesData] = await Promise.all([
        coreService.getSpectacle(spectacleId),
        coreService.getSeances({
          spectacle_id: spectacleId,
          upcoming_only: true,
          status: 'scheduled',
        }),
      ]);

      setSpectacle(spectacleData);
      setSeances(seancesData.data || []);
    } catch (error) {
      console.error('Erreur chargement spectacle:', error);
      toaster.error({
        title: "Erreur",
        description: "Impossible de charger le spectacle",
      });
      navigate("/");
    } finally {
      setLoading(false);
    }
  };

  const handleReserve = (seanceId: number) => {
    if (!isAuthenticated) {
      toaster.error({
        title: "Connexion requise",
        description: "Veuillez vous connecter pour réserver",
      });
      navigate("/login");
      return;
    }
    navigate(`/reservation/${seanceId}`);
  };

  if (loading) {
    return (
      <Center minH="70vh">
        <Spinner size="xl" color="red.500" />
      </Center>
    );
  }

  if (!spectacle) {
    return (
      <Center minH="70vh">
        <Text>Spectacle introuvable</Text>
      </Center>
    );
  }

  return (
    <Box w="full" maxW="1200px" mx="auto" py={8} px={4}>
      <Grid templateColumns={{ base: "1fr", md: "1fr 2fr" }} gap={8}>
        <Box>
          <Image
            src={spectacle.poster_url || spectacle.image_url || theatreImg}
            alt={spectacle.title}
            borderRadius="lg"
            w="full"
            objectFit="cover"
          />
        </Box>

        <Stack gap={4}>
          <Flex align="center" gap={4}>
            <Heading size="2xl">{spectacle.title}</Heading>
            {spectacle.category && (
              <Badge colorScheme="red" fontSize="md" px={3} py={1} borderRadius="full">
                {spectacle.category.name}
              </Badge>
            )}
          </Flex>

          {spectacle.description && (
            <Text fontSize="lg" color="gray.300">
              {spectacle.description}
            </Text>
          )}

          <Grid templateColumns="repeat(2, 1fr)" gap={4} mt={4}>
            {spectacle.director && (
              <Box>
                <Text fontWeight="bold" color="red.400">Réalisateur</Text>
                <Text>{spectacle.director}</Text>
              </Box>
            )}

            {spectacle.duration && (
              <Box>
                <Text fontWeight="bold" color="red.400">Durée</Text>
                <Text>{spectacle.duration} minutes</Text>
              </Box>
            )}

            {spectacle.language && (
              <Box>
                <Text fontWeight="bold" color="red.400">Langue</Text>
                <Text>{spectacle.language.toUpperCase()}</Text>
              </Box>
            )}

            {spectacle.age_restriction && (
              <Box>
                <Text fontWeight="bold" color="red.400">Âge minimum</Text>
                <Text>{spectacle.age_restriction} ans</Text>
              </Box>
            )}
          </Grid>

          {spectacle.actors && spectacle.actors.length > 0 && (
            <Box mt={4}>
              <Text fontWeight="bold" color="red.400">Distribution</Text>
              <Text>{spectacle.actors.join(", ")}</Text>
            </Box>
          )}

          <Box mt={4}>
            <Text fontSize="3xl" fontWeight="bold" color="red.400">
              À partir de {spectacle.base_price}€
            </Text>
          </Box>
        </Stack>
      </Grid>

      <Box mt={12}>
        <Heading size="lg" mb={6}>
          Séances disponibles ({seances.length})
        </Heading>

        {seances.length === 0 ? (
          <Center minH="200px">
            <Text color="gray.500">Aucune séance prévue pour ce spectacle</Text>
          </Center>
        ) : (
          <Grid templateColumns={{ base: "1fr", md: "repeat(2, 1fr)", lg: "repeat(3, 1fr)" }} gap={4}>
            {seances.map((seance) => (
              <Card.Root key={seance.id} bg="gray.800">
                <Card.Body>
                  <Text fontWeight="bold" fontSize="lg">
                    {new Date(seance.date_seance).toLocaleDateString("fr-FR", {
                      weekday: "long",
                      day: "2-digit",
                      month: "long",
                      year: "numeric",
                    })}
                  </Text>
                  <Text color="red.400" fontSize="xl" fontWeight="bold">
                    {new Date(seance.date_seance).toLocaleTimeString("fr-FR", {
                      hour: "2-digit",
                      minute: "2-digit",
                    })}
                  </Text>
                  {seance.hall && (
                    <Text color="gray.400" fontSize="sm">
                      {seance.hall.name}
                    </Text>
                  )}
                  <Text color="gray.400" fontSize="sm">
                    {seance.remaining_seats || seance.available_seats} places disponibles
                  </Text>
                  <Text fontSize="xl" fontWeight="bold" mt={2}>
                    {seance.price}€
                  </Text>
                </Card.Body>
                <Card.Footer>
                  <Button
                    w="full"
                    bg="red.800"
                    color="white"
                    _hover={{ bg: "red.700" }}
                    onClick={() => handleReserve(seance.id)}
                    isDisabled={(seance.remaining_seats || seance.available_seats) === 0}
                  >
                    {(seance.remaining_seats || seance.available_seats) === 0
                      ? "Complet"
                      : "Réserver"}
                  </Button>
                </Card.Footer>
              </Card.Root>
            ))}
          </Grid>
        )}
      </Box>
    </Box>
  );
}
