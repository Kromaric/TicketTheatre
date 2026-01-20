import { Button, Card, Image, Text } from "@chakra-ui/react";
import theatreImg from "../assets/theatre.png";

export const TicketCard = () => {
  return (
    <Card.Root
      maxW="sm"
      overflow="hidden"
      bg="yellow.500"
      borderWidth="1px"
      borderColor="yellow.500"
      borderRadius="lg"
    >
      <Card.Body gap="2" p="0">
        <Card.Title
          textAlign="center"
          textTransform="uppercase"
          color="black"
          px="4"
          pt="4"
          pb="2"
        >
          Titre piece
        </Card.Title>

        <Image
          src={theatreImg}
          alt="Scène du théâtre Le Velum"
          w="100%"
          objectFit="cover"
          display="block"
        />

        <Card.Description color="black" px="4" pt="2">
          Réalisateur
        </Card.Description>
        <Card.Description color="black" px="4">
          Acteur
        </Card.Description>
        <Card.Description color="black" px="4" pb="2">
          Genre
        </Card.Description>

        <Text
          textStyle="2xl"
          fontWeight="medium"
          letterSpacing="tight"
          mt="2"
          color="black"
          px="4"
          pb="4"
        >
          15 €
        </Text>
      </Card.Body>

      <Card.Footer gap="2" justifyContent="center" pb="4">
        <Button
          w="full"
          bg="red.800"
          color="white"
          _hover={{ bg: "red.700" }}
        >
          Réserver
        </Button>
      </Card.Footer>
    </Card.Root>
  );
};
