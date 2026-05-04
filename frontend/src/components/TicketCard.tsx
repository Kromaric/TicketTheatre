import { Button, Card, Image, Text, Badge, Box, Stack } from "@chakra-ui/react";
import { useNavigate } from "react-router-dom";
import type { Spectacle } from "../services/core.service";
import theatreImg from "../assets/theatre.png";

interface TicketCardProps {
  spectacle: Spectacle;
}

export const TicketCard = ({ spectacle }: TicketCardProps) => {
  const navigate = useNavigate();

  //console.log('TicketCard rendering for:', spectacle.title);

  const handleReserve = () => {
    console.log("Réserver spectacle:", spectacle.id);
    navigate(`/spectacle/${spectacle.id}`);
  };

  try {
    return (
      <Card.Root
        maxW="sm"
        w="full"
        overflow="hidden"
        bg="yellow.500"
        borderWidth="1px"
        borderColor="yellow.500"
        borderRadius="lg"
      >
        <Card.Body gap="2" p="0">
          <Box position="relative">
            <Card.Title
              textAlign="center"
              textTransform="uppercase"
              color="black"
              px="4"
              pt="4"
              pb="2"
              fontSize="lg"
              minH="60px"
            >
              {spectacle.title || "Sans titre"}
            </Card.Title>

            {spectacle.category?.name && (
              <Badge
                position="absolute"
                top="2"
                right="2"
                colorScheme="red"
                borderRadius="full"
                px="2"
              >
                {spectacle.category.name}
              </Badge>
            )}
          </Box>

          <Image
            src={spectacle.image_url || theatreImg}
            alt={spectacle.title}
            w="100%"
            h="200px"
            objectFit="cover"
            display="block"
            onError={(e) => {
              console.log("Image error for:", spectacle.title);
              (e.target as HTMLImageElement).src = theatreImg;
            }}
          />

          <Stack gap="1" px="4" pt="2" minH="120px">
            {spectacle.director && (
              <Text color="black" fontSize="sm">
                <strong>Réalisateur:</strong> {spectacle.director}
              </Text>
            )}

            {spectacle.actors && spectacle.actors.length > 0 && (
              <Text color="black" fontSize="sm">
                <strong>Acteurs:</strong>{" "}
                {spectacle.actors.slice(0, 2).join(", ")}
                {spectacle.actors.length > 2 && "..."}
              </Text>
            )}

            {spectacle.duration && (
              <Text color="black" fontSize="sm">
                <strong>Durée:</strong> {spectacle.duration} min
              </Text>
            )}

            {spectacle.description && (
              <Text color="black" fontSize="sm">
                {spectacle.description.substring(0, 100)}
                {spectacle.description.length > 100 && "..."}
              </Text>
            )}
          </Stack>

          <Text
            fontSize="2xl"
            fontWeight="medium"
            letterSpacing="tight"
            mt="2"
            color="black"
            px="4"
            pb="4"
          >
            À partir de {spectacle.base_price || 0} €
          </Text>
        </Card.Body>

        <Card.Footer gap="2" justifyContent="center" pb="4" px="4">
          <Button
            w="full"
            bg="red.800"
            color="white"
            _hover={{ bg: "red.700" }}
            onClick={handleReserve}
          >
            Réserver
          </Button>
        </Card.Footer>
      </Card.Root>
    );
  } catch (error) {
    console.error(
      "Erreur dans TicketCard:",
      error,
      "pour spectacle:",
      spectacle,
    );
    return (
      <Box bg="red.100" p={4} borderRadius="md">
        <Text color="red.800">Erreur d'affichage du spectacle</Text>
        <Text fontSize="sm">{spectacle.title}</Text>
      </Box>
    );
  }
};
