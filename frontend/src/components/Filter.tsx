import { useState } from "react";
import { Button, Card, NativeSelect, Stack } from "@chakra-ui/react";

type FilterValues = {
  genre: string;
  metteurEnScene: string;
  date: string;
};

export const Filter = ({
  onValidate,
}: {
  onValidate?: (values: FilterValues) => void;
}) => {
  const [genre, setGenre] = useState("");
  const [metteurEnScene, setMetteurEnScene] = useState("");
  const [date, setDate] = useState("");

  const handleValidate = () => {
    onValidate?.({ genre, metteurEnScene, date });
  };

  return (
    <Card.Root
      maxW="260px"
      bg="red.800"
      color="white"
      borderRadius="md"
      p="3"
      pt="0.5"
    >
      {/* + espace sous le titre */}
      <Card.Header pb="8">
        <Card.Title
          textAlign="center"
          textTransform="uppercase"
          fontSize="sm"
          color="white"
        >
          FILTRER PAR
        </Card.Title>
      </Card.Header>

      {/* + espace au-dessus et en dessous des filtres */}
      <Card.Body pt="3" pb="4">
        <Stack gap="3" w="full">
          <NativeSelect.Root size="xs" variant="outline" w="full">
            <NativeSelect.Field
              bg="white"
              color="black"
              borderRadius="full"
              fontSize="0.6rem"
              textTransform="uppercase"
              value={genre}
              onChange={(e) => setGenre(e.target.value)}
            >
              <option value="" disabled>
                GENRE
              </option>
            </NativeSelect.Field>
            <NativeSelect.Indicator color="black" />
          </NativeSelect.Root>

          <NativeSelect.Root size="xs" variant="outline" w="full">
            <NativeSelect.Field
              bg="white"
              color="black"
              borderRadius="full"
              fontSize="0.6rem"
              textTransform="uppercase"
              value={metteurEnScene}
              onChange={(e) => setMetteurEnScene(e.target.value)}
            >
              <option value="" disabled>
                METTEUR EN SCENE
              </option>
            </NativeSelect.Field>
            <NativeSelect.Indicator color="black" />
          </NativeSelect.Root>

          <NativeSelect.Root size="xs" variant="outline" w="full">
            <NativeSelect.Field
              bg="white"
              color="black"
              borderRadius="full"
              fontSize="0.6rem"
              textTransform="uppercase"
              value={date}
              onChange={(e) => setDate(e.target.value)}
            >
              <option value="" disabled>
                DATE
              </option>
            </NativeSelect.Field>
            <NativeSelect.Indicator color="black" />
          </NativeSelect.Root>
        </Stack>
      </Card.Body>

      {/* + espace au-dessus du bouton */}
      <Card.Footer justifyContent="center" pt="8" pb="2">
        <Button
          onClick={handleValidate}
          bg="yellow.500"
          color="black"
          borderRadius="full"
          size="xs"
          fontSize="0.6rem"
          textTransform="uppercase"
          px="6"
        >
          VALIDER
        </Button>
      </Card.Footer>
    </Card.Root>
  );
};
